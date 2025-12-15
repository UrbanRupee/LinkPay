<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Payment_request;
use App\Models\PayoutRequest;
use App\Models\Wallet;

/**
 * TRUE Reconciliation Controller
 * 
 * PHILOSOPHY: TRUST NOTHING, VERIFY EVERYTHING
 * 
 * Rules:
 * 1. Wallet balance is THE source of truth
 * 2. NEVER add money without callback confirmation
 * 3. NEVER subtract money without deduction record
 * 4. Flag mismatches for manual review
 * 5. No auto-corrections - only reporting
 */
class TrueReconciliation extends Controller
{
    /**
     * Main reconciliation dashboard
     * Shows ACTUAL state vs CLAIMED state
     */
    public function dashboard()
    {
        $users = DB::table('users')
            ->whereIn('role', ['user', 'admin'])
            ->where('status', 1)
            ->get();

        $results = [];

        foreach ($users as $user) {
            $analysis = $this->analyzeUser($user->userid);
            $results[] = $analysis;
        }

        return view('admin.true_reconciliation', compact('results'));
    }

    /**
     * Analyze user - Truth-based approach
     */
    private function analyzeUser($userid)
    {
        // Get current wallet (SOURCE OF TRUTH)
        $wallet = Wallet::where('userid', $userid)->first();
        $actualPayin = $wallet ? $wallet->payin : 0;
        $actualPayout = $wallet ? $wallet->payout : 0;

        // Get CLAIMED successful payments
        $claimedSuccess = DB::table('payment_requests')
            ->where('userid', $userid)
            ->where('status', 1)
            ->selectRaw('COUNT(*) as count, SUM(amount - tax) as net')
            ->first();

        $claimedAmount = $claimedSuccess->net ?? 0;
        $claimedCount = $claimedSuccess->count ?? 0;

        // Get payments WITH wallet credit confirmation (via addwallet function)
        // The addwallet() function updates wallet directly, so if wallet has money,
        // it means callbacks ran successfully
        
        // For verification: Check if there are payment_requests marked success
        // but wallet doesn't have the corresponding amount
        $discrepancy = $actualPayin - $claimedAmount;

        // Get pending transactions
        $pending = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->count();

        $pendingOld = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subMinutes(15))
            ->count();

        // Get user info
        $user = DB::table('users')->where('userid', $userid)->first();

        return [
            'userid' => $userid,
            'name' => $user->name ?? '',
            'gateway' => $user->payingateway,
            'gateway_name' => gateway_name($user->payingateway, 'payin'),
            
            // TRUTH (What actually exists)
            'actual_payin' => $actualPayin,
            'actual_payout' => $actualPayout,
            
            // CLAIMS (What system says should exist)
            'claimed_payin' => $claimedAmount,
            'claimed_count' => $claimedCount,
            
            // VERIFICATION
            'discrepancy' => $discrepancy,
            'status' => $this->getStatus($discrepancy),
            
            // PENDING ANALYSIS
            'pending_total' => $pending,
            'pending_old' => $pendingOld,
            
            // FLAGS
            'needs_attention' => abs($discrepancy) > 10 || $pendingOld > 20,
            'issue_type' => $this->getIssueType($discrepancy, $pendingOld)
        ];
    }

    private function getStatus($discrepancy)
    {
        if (abs($discrepancy) < 1) {
            return 'perfect';
        } elseif ($discrepancy > 0) {
            return 'over_credited'; // Wallet has MORE than claims
        } else {
            return 'under_credited'; // Wallet has LESS than claims
        }
    }

    private function getIssueType($discrepancy, $pendingOld)
    {
        if (abs($discrepancy) < 1 && $pendingOld < 10) {
            return 'none';
        }

        $issues = [];

        if ($discrepancy > 10) {
            $issues[] = 'wallet_over_credited';
        } elseif ($discrepancy < -10) {
            $issues[] = 'missing_callbacks';
        }

        if ($pendingOld > 20) {
            $issues[] = 'callback_failures';
        }

        return empty($issues) ? 'minor' : implode(', ', $issues);
    }

    /**
     * Detailed investigation for specific user
     */
    public function investigate($userid)
    {
        $wallet = Wallet::where('userid', $userid)->first();
        
        // Get ALL payment_requests
        $allPayments = DB::table('payment_requests')
            ->where('userid', $userid)
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(amount) as gross,
                SUM(tax) as tax,
                SUM(amount - tax) as net
            ')
            ->groupBy('status')
            ->get();

        // Get pending payments that might have been paid but callback missing
        $suspiciousPayments = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subHours(1))
            ->orderBy('id', 'DESC')
            ->limit(100)
            ->get()
            ->map(function($p) {
                return [
                    'transaction_id' => $p->transaction_id,
                    'amount' => $p->amount,
                    'tax' => $p->tax,
                    'net' => $p->amount - $p->tax,
                    'created_at' => $p->created_at,
                    'age_hours' => now()->diffInHours($p->created_at),
                    'has_utr' => !empty($p->data1) || !empty($p->data4),
                    'utr' => $p->data1 ?? $p->data4 ?? null
                ];
            });

        return response()->json([
            'userid' => $userid,
            'wallet' => [
                'payin' => $wallet->payin ?? 0,
                'payout' => $wallet->payout ?? 0
            ],
            'payments_summary' => $allPayments,
            'suspicious_pending' => $suspiciousPayments,
            'recommendations' => $this->getRecommendations($wallet, $allPayments, $suspiciousPayments)
        ]);
    }

    private function getRecommendations($wallet, $payments, $suspicious)
    {
        $recommendations = [];

        // Check for payments with UTR but still pending
        $hasUtrButPending = $suspicious->filter(function($p) {
            return $p['has_utr'] === true;
        });

        if ($hasUtrButPending->count() > 0) {
            $recommendations[] = [
                'type' => 'critical',
                'issue' => 'Payments with UTR but still pending',
                'count' => $hasUtrButPending->count(),
                'action' => 'These payments were likely successful. Check gateway and manually mark as success + credit wallet.',
                'affected' => $hasUtrButPending->pluck('transaction_id')->toArray()
            ];
        }

        // Check for old pending without UTR
        $oldNoUtr = $suspicious->filter(function($p) {
            return $p['has_utr'] === false && $p['age_hours'] > 24;
        });

        if ($oldNoUtr->count() > 0) {
            $recommendations[] = [
                'type' => 'warning',
                'issue' => 'Old pending payments without UTR',
                'count' => $oldNoUtr->count(),
                'action' => 'Likely failed/abandoned. Query gateway API to confirm, then mark as failed (NO refund needed - wallet was never credited).'
            ];
        }

        return $recommendations;
    }
}


