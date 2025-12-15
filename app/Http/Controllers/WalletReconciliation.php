<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Payment_request;
use App\Models\Wallet;
use App\Models\Logs;
use App\Models\Transaction;

/**
 * Wallet Reconciliation Controller
 * Monitors and auto-fixes wallet balance discrepancies
 * Handles NSO callback issues and payment reconciliation
 */
class WalletReconciliation extends Controller
{
    /**
     * Main reconciliation page - analyze all users
     */
    public function maintainPayinBalance()
    {
        // ✅ ONLY SHOW MERCHANT/USER ACCOUNTS (Exclude admin accounts)
        $users = DB::table('users')
            ->where('role', 'user') // ONLY merchants/users, NO admins
            ->where('status', 1)
            ->get();

        $results = [];
        $issues = [];

        foreach ($users as $user) {
            $analysis = $this->analyzeUserWallet($user->userid);
            
            $results[] = $analysis;
            
            // Flag issues
            if (abs($analysis['difference']) > 1) { // More than ₹1 difference
                $issues[] = $analysis;
            }
        }

        return view('admin.wallet_reconciliation', compact('results', 'issues'));
    }

    /**
     * Analyze single user wallet
     * CORRECTED: Uses payment_requests table as source of truth (matches how addwallet works)
     */
    private function analyzeUserWallet($userid)
    {
        try {
        // Get current wallet
        $wallet = Wallet::where('userid', $userid)->first();
            $currentPayin = $wallet ? (float) $wallet->payin : 0;
            $currentPayout = $wallet ? (float) $wallet->payout : 0;

        // CORRECT METHOD: Calculate from payment_requests (this is what addwallet uses!)
        $payinCalc = DB::table('payment_requests')
            ->where('userid', $userid)
            ->where('status', 1)
            ->selectRaw('SUM(amount - tax) as total_net, COUNT(*) as count')
            ->first();

            $calculatedPayin = (float) ($payinCalc->total_net ?? 0);
            $successCount = (int) ($payinCalc->count ?? 0);

        // Check for transaction credits (for audit/comparison only)
        $txnCredits = DB::table('transactions')
            ->where('userid', $userid)
            ->where('category', 'payin')
            ->where('type', 'credit')
            ->selectRaw('COUNT(*) as count')
            ->first();

            $txnCreditCount = (int) ($txnCredits->count ?? 0);

        // Check settlements (money transferred FROM PayIn TO PayOut)
        // Settlements are recorded as 'settlement' credit (credit to payout wallet)
        // Amount deducted from payin = gross amount WITHOUT tax
        // For NEW settlements: data3 = gross amount WITHOUT tax (correct)
        // For OLD settlements: data3 might contain gross WITH tax, need to detect and fix
        // Detection: If data3 ≈ (amount + data4 + data5), then data3 is gross WITH tax (old format)
        $settlements = DB::table('transactions')
            ->where('userid', $userid)
            ->where('category', 'settlement')
            ->where('type', 'credit')
            ->get();
        
        $totalSettled = 0;
        foreach ($settlements as $settlement) {
            $data3 = (float) ($settlement->data3 ?? 0);
            $data4 = (float) ($settlement->data4 ?? 0);
            $data5 = (float) ($settlement->data5 ?? 0);
            $amount = (float) ($settlement->amount ?? 0);
            
            if ($data3 > 0) {
                // Check if data3 is gross WITH tax (old format)
                // If data3 is approximately equal to (amount + data4 + data5), it's gross WITH tax
                $expectedGrossWithTax = $amount + $data4 + $data5;
                $difference = abs($data3 - $expectedGrossWithTax);
                
                if ($difference < 10) { // Within ₹10 tolerance - likely old format (gross WITH tax)
                    // Old format: data3 contains gross WITH tax, so subtract tax to get gross WITHOUT tax
                    $grossWithoutTax = $data3 - $data4;
                    $totalSettled += $grossWithoutTax;
                } else {
                    // New format: data3 contains gross WITHOUT tax
                    $totalSettled += $data3;
                }
            } else {
                // No data3: calculate from amount + tax + hold
                $totalSettled += $amount + $data4 + $data5;
            }
        }
        
            $settledAmount = (float) $totalSettled;

        // Check for other payin wallet debits (admin deductions, manual adjustments, etc.)
        // Admin deductions are stored with category='admin_deduction', type='debit', and data1=data2='payin'
        $otherDebits = DB::table('transactions')
            ->where('userid', $userid)
            ->where('type', 'debit')
            ->where('category', '!=', 'settlement') // Exclude settlements (already counted above)
            ->where(function($query) {
                // Admin deductions affecting payin wallet (data1 and data2 both contain wallet type)
                $query->where(function($q) {
                    $q->where('category', 'admin_deduction')
                      ->where(function($subQ) {
                          $subQ->where('data1', 'payin')
                               ->orWhere('data2', 'payin');
                      });
                })
                // Any other debit transactions that might affect payin wallet
                ->orWhere(function($q) {
                    $q->where('category', '!=', 'admin_deduction')
                      ->where(function($subQ) {
                          $subQ->where('data1', 'payin')
                               ->orWhere('data2', 'payin');
                      });
                });
            })
            ->selectRaw('SUM(amount) as total_debits')
            ->first();
        
        $otherDebitAmount = (float) ($otherDebits->total_debits ?? 0);

        // Expected PayIn = Successful PayIns - Settlements - Other Debits
        $expectedPayin = $calculatedPayin - $settledAmount - $otherDebitAmount;
        $difference = $expectedPayin - $currentPayin;

        // Check for pending transactions across ALL gateways
        $user = DB::table('users')->where('userid', $userid)->first();
        $gateway = $user ? $user->payingateway : null;
        
        $pendingCount = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subMinutes(10))
            ->count();

        // Detect missing transaction logs (audit purpose)
        $missingLogs = $successCount - $txnCreditCount;

            // Safely get gateway name
            $gatewayName = 'Unknown';
            try {
                if ($gateway && function_exists('gateway_name')) {
                    $gatewayName = gateway_name($gateway, 'payin') ?: 'Unknown';
                }
            } catch (\Exception $e) {
                Log::warning('Error getting gateway name', ['gateway' => $gateway, 'error' => $e->getMessage()]);
            }

        return [
            'userid' => $userid,
            'current_payin' => $currentPayin,
            'calculated_payin' => $calculatedPayin, // From payment_requests
            'settled_amount' => $settledAmount,
            'other_debits' => $otherDebitAmount,
            'expected_payin' => $expectedPayin,
            'difference' => $difference,
            'current_payout' => $currentPayout,
            'pending_count' => $pendingCount,
            'pending_easebuzz' => $gateway == '28' ? $pendingCount : 0, // ✅ EASEBUZZ ONLY
            'missing_logs' => $missingLogs,
            'gateway' => $gateway,
                'gateway_name' => $gatewayName,
            'has_issue' => abs($difference) > 1 || $pendingCount > 10
        ];
        } catch (\Exception $e) {
            Log::error('Error analyzing wallet for userid: ' . $userid, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return default values on error
            return [
                'userid' => $userid,
                'current_payin' => 0,
                'calculated_payin' => 0,
                'settled_amount' => 0,
                'other_debits' => 0,
                'expected_payin' => 0,
                'difference' => 0,
                'current_payout' => 0,
                'pending_count' => 0,
                'pending_easebuzz' => 0,
                'missing_logs' => 0,
                'gateway' => null,
                'gateway_name' => 'Error',
                'has_issue' => true,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Auto-fix wallet discrepancies
     */
    public function autoFixWallet(Request $request, $userid)
    {
        DB::beginTransaction();
        try {
            $analysis = $this->analyzeUserWallet($userid);
            
            if (abs($analysis['difference']) < 0.01) {
                return response()->json([
                    'status' => false,
                    'message' => 'No significant difference to fix'
                ]);
            }

            // Update wallet to expected value
            Wallet::where('userid', $userid)->update([
                'payin' => $analysis['expected_payin']
            ]);

            // Log the correction
            Logs::create([
                'uniqueid' => 'WalletFix_' . $userid . '_' . now()->timestamp,
                'value' => json_encode([
                    'old_balance' => $analysis['current_payin'],
                    'new_balance' => $analysis['expected_payin'],
                    'difference' => $analysis['difference'],
                    'reason' => 'Auto-correction from reconciliation'
                ]),
                'data1' => 'wallet_fix'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Wallet fixed successfully',
                'old_balance' => $analysis['current_payin'],
                'new_balance' => $analysis['expected_payin'],
                'difference' => $analysis['difference']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet Fix Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check and fix pending transactions for Easebuzz ONLY
     */
    public function checkPending($userid)
    {
        $user = DB::table('users')->where('userid', $userid)->first();
        
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found']);
        }

        $gateway = $user->payingateway;

        // Route to appropriate gateway checker - EASEBUZZ ONLY
        switch ($gateway) {
            case '28': // ✅ Easebuzz - ONLY ACTIVE GATEWAY
                return $this->checkEasebuzzPending($userid);
            
            // ALL OTHER GATEWAYS DISABLED
            // case '26': // NSO
            //     return $this->checkNSOPending($userid);
            // case '24': // UnitPayGo
            //     return $this->checkUnitPayGoPending($userid);
            // case '14': // FinQunes
            //     return $this->checkFinQunesPending($userid);
            // case '23': // PayU
            //     return $this->checkPayUPending($userid);
            
            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Gateway ' . gateway_name($gateway, 'payin') . ' not supported (Only Easebuzz active)'
                ]);
        }
    }

    /**
     * ✅ Check and fix Easebuzz pending transactions (PayIn)
     * Simple working functionality for Easebuzz PayIn reconciliation
     */
    private function checkEasebuzzPending($userid)
    {
        $user = DB::table('users')->where('userid', $userid)->first();
        
        if (!$user || $user->payingateway != '28') {
            return response()->json([
                'status' => false,
                'message' => 'User is not using Easebuzz gateway'
            ]);
        }

        // Get old pending transactions (>10 minutes)
        $pendingTxns = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subMinutes(10))
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        $checked = 0;
        $updated = 0;
        $failed = 0;
        $results = [];

        foreach ($pendingTxns as $txn) {
            $checked++;
            
            // Check status with Easebuzz API
            $status = $this->checkEasebuzzStatus($txn->transaction_id, $txn->amount);
            
            if ($status['status'] === 'success') {
                // Credit wallet
                $netAmount = $txn->amount - $txn->tax;
                addwallet($userid, $netAmount, '+', 'payin');
                
                // Update transaction
                Payment_request::where('id', $txn->id)->update([
                    'status' => 1,
                    'data4' => $status['utr'] ?? '00'
                ]);
                
                // Add transaction log
                addtransaction(
                    $userid,
                    'payin',
                    'credit',
                    $netAmount,
                    'Easebuzz PayIn Reconciled: ' . $txn->transaction_id,
                    1,
                    $txn->transaction_id,
                    $status['utr'] ?? '00'
                );
                
                $updated++;
                $results[] = [
                    'txn_id' => $txn->transaction_id,
                    'status' => 'updated_to_success',
                    'amount' => $netAmount,
                    'utr' => $status['utr'] ?? '00'
                ];
                
                Log::info('✅ Easebuzz PayIn Reconciled to Success', [
                    'txn_id' => $txn->transaction_id,
                    'userid' => $userid,
                    'amount' => $netAmount
                ]);
                
            } elseif ($status['status'] === 'failed') {
                Payment_request::where('id', $txn->id)->update([
                    'status' => 2,
                    'data4' => $status['utr'] ?? '00'
                ]);
                $failed++;
                $results[] = [
                    'txn_id' => $txn->transaction_id,
                    'status' => 'marked_failed',
                    'easebuzz_status' => $status['easebuzz_status'] ?? 'failed'
                ];
                
                Log::info('❌ Easebuzz PayIn Reconciled to Failed', [
                    'txn_id' => $txn->transaction_id,
                    'userid' => $userid
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'gateway' => 'Easebuzz',
            'checked' => $checked,
            'updated' => $updated,
            'failed' => $failed,
            'results' => $results
        ]);
    }

    /**
     * ✅ Check Easebuzz PayIn transaction status via API
     */
    private function checkEasebuzzStatus($transactionId, $amount)
    {
        try {
            // Easebuzz Configuration
            $merchantKey = 'AEFQ63QEFK';
            $salt = 'BMHVGJZTOJ';
            $env = 'prod'; // production
            $baseUrl = $env === 'prod' ? 'https://pay.easebuzz.in' : 'https://testpay.easebuzz.in';
            
            // Generate hash for transaction retrieval
            // hash = key|txnid|amount|salt
            $hashString = $merchantKey . '|' . $transactionId . '|' . $amount . '|' . $salt;
            $hash = hash('sha512', $hashString);
            
            Log::info('🔍 Easebuzz PayIn Status Check', [
                'txn_id' => $transactionId,
                'amount' => $amount
            ]);
            
            // Call Easebuzz Transaction Status API
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $baseUrl . '/transaction/v2.1/retrieve',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query([
                    'key' => $merchantKey,
                    'txnid' => $transactionId,
                    'amount' => $amount,
                    'email' => 'support@xpaisa.in',
                    'phone' => '9999999999',
                    'hash' => $hash
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Log::error('Easebuzz PayIn Status Check Error', ['error' => $error]);
                return ['status' => 'error', 'message' => $error];
            }

            $data = json_decode($response);
            
            Log::info('Easebuzz PayIn Status Response', [
                'txn_id' => $transactionId,
                'response' => $data
            ]);
            
            if (isset($data->status)) {
                $txnStatus = strtolower($data->status);
                $utr = $data->bank_ref_num ?? $data->easepayid ?? '00';
                
                // SUCCESS
                if ($txnStatus === 'success' || $txnStatus === 'completed') {
                    return [
                        'status' => 'success',
                        'utr' => $utr,
                        'easebuzz_status' => $txnStatus
                    ];
                }
                
                // FAILURE
                if ($txnStatus === 'failure' || $txnStatus === 'failed' || $txnStatus === 'usercancelled' || $txnStatus === 'dropped') {
                    return [
                        'status' => 'failed',
                        'utr' => $utr,
                        'easebuzz_status' => $txnStatus
                    ];
                }
            }

            // Still pending or unknown
            return ['status' => 'pending'];

        } catch (\Exception $e) {
            Log::error('Easebuzz PayIn Status Check Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check and fix NSO pending transactions - DISABLED
     */
    public function checkNSOPending($userid)
    {
        $user = DB::table('users')->where('userid', $userid)->first();
        
        if (!$user || $user->payingateway != '26') {
            return response()->json([
                'status' => false,
                'message' => 'User is not using NSO gateway'
            ]);
        }

        // Get old pending transactions (>10 minutes)
        $pendingTxns = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subMinutes(10))
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        $checked = 0;
        $updated = 0;
        $failed = 0;
        $results = [];

        foreach ($pendingTxns as $txn) {
            $checked++;
            
            // Check status with NSO API
            $status = $this->checkNSOStatus($txn->transaction_id);
            
            if ($status['status'] === 'success') {
                // Credit wallet
                $netAmount = $txn->amount - $txn->tax;
                addwallet($userid, $netAmount, '+', 'payin');
                
                // Update transaction
                Payment_request::where('id', $txn->id)->update([
                    'status' => 1,
                    'data4' => $status['utr'] ?? '00'
                ]);
                
                $updated++;
                $results[] = [
                    'txn_id' => $txn->transaction_id,
                    'status' => 'updated_to_success',
                    'amount' => $netAmount
                ];
                
            } elseif ($status['status'] === 'failed') {
                Payment_request::where('id', $txn->id)->update([
                    'status' => 2
                ]);
                $failed++;
                $results[] = [
                    'txn_id' => $txn->transaction_id,
                    'status' => 'marked_failed'
                ];
            }
        }

        return response()->json([
            'status' => true,
            'checked' => $checked,
            'updated' => $updated,
            'failed' => $failed,
            'results' => $results
        ]);
    }

    /**
     * Check NSO transaction status via API
     */
    private function checkNSOStatus($transactionId)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.odinpe.in/api/check-payment-status',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'client_id' => 'NSO100011',
                    'txn_id' => $transactionId
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                return ['status' => 'error', 'message' => $error];
            }

            $data = json_decode($response);
            
            if (isset($data->status) && strtolower($data->status) === 'success' && isset($data->data)) {
                $paymentStatus = strtolower($data->data->status ?? '');
                $utr = $data->data->utr ?? '00';
                
                if ($paymentStatus === 'success' || $paymentStatus === 'completed') {
                    return ['status' => 'success', 'utr' => $utr];
                } elseif ($paymentStatus === 'failed') {
                    return ['status' => 'failed'];
                }
            }

            return ['status' => 'pending'];

        } catch (\Exception $e) {
            Log::error('NSO Status Check Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Bulk reconcile all users with issues (EXCLUDE ADMINS)
     */
    public function bulkReconcile(Request $request)
    {
        $minDifference = $request->input('min_difference', 1);
        
        // ✅ ONLY reconcile merchant/user accounts (Exclude admin accounts)
        $users = DB::table('users')
            ->where('role', 'user') // ONLY merchants/users, NO admins
            ->where('status', 1)
            ->get();

        $fixed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($users as $user) {
            $analysis = $this->analyzeUserWallet($user->userid);
            
            if (abs($analysis['difference']) >= $minDifference) {
                try {
                    Wallet::where('userid', $user->userid)->update([
                        'payin' => $analysis['expected_payin']
                    ]);
                    
                    Logs::create([
                        'uniqueid' => 'BulkWalletFix_' . $user->userid . '_' . now()->timestamp,
                        'value' => json_encode($analysis),
                        'data1' => 'bulk_wallet_fix'
                    ]);
                    
                    $fixed++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'userid' => $user->userid,
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'status' => true,
            'fixed' => $fixed,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    }

    /**
     * ✅ Monitor Easebuzz callback issues (ONLY ACTIVE GATEWAY)
     */
    public function monitorEasebuzzCallbacks()
    {
        // Find users using Easebuzz gateway
        $easebuzzUsers = DB::table('users')
            ->where('payingateway', '28') // ✅ EASEBUZZ ONLY
            ->where('status', 1)
            ->get();

        $summary = [];

        foreach ($easebuzzUsers as $user) {
            $pending = Payment_request::where('userid', $user->userid)
                ->where('status', 0)
                ->where('created_at', '<=', now()->subMinutes(10))
                ->count();

            $oldPending = Payment_request::where('userid', $user->userid)
                ->where('status', 0)
                ->where('created_at', '<=', now()->subHours(1))
                ->count();

            if ($pending > 0 || $oldPending > 0) {
                $summary[] = [
                    'userid' => $user->userid,
                    'name' => $user->name,
                    'pending_10min' => $pending,
                    'pending_1hour' => $oldPending,
                    'issue' => $oldPending > 5 ? 'critical' : ($pending > 10 ? 'warning' : 'normal')
                ];
            }
        }

        return response()->json([
            'status' => true,
            'gateway' => 'Easebuzz',
            'easebuzz_users' => count($easebuzzUsers),
            'users_with_pending' => count($summary),
            'details' => $summary
        ]);
    }
    
    /**
     * Monitor NSO callback issues - DISABLED (Use monitorEasebuzzCallbacks)
     */
    public function monitorNSOCallbacks()
    {
        return response()->json([
            'status' => false,
            'message' => 'NSO gateway disabled. Only Easebuzz active. Use monitorEasebuzzCallbacks() instead.'
        ]);
    }

    /**
     * Get detailed diagnostic breakdown for a specific user
     */
    public function getDiagnostic($userid)
    {
        // Set JSON response header first
        header('Content-Type: application/json');
        
        try {
            // Sanitize userid
            $userid = trim($userid);
            if (empty($userid)) {
                return response()->json([
                    'status' => false,
                    'error' => 'Invalid userid',
                    'userid' => $userid
                ], 400);
            }

            // Validate userid exists
            $user = DB::table('users')->where('userid', $userid)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'error' => 'User not found',
                    'userid' => $userid
                ], 404);
            }

            // Try to analyze wallet with error handling
            try {
        $analysis = $this->analyzeUserWallet($userid);
            } catch (\Exception $e) {
                Log::error('Error in analyzeUserWallet for userid: ' . $userid, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => false,
                    'error' => 'Error analyzing wallet',
                    'message' => $e->getMessage(),
                    'userid' => $userid
                ], 500);
            }
            
            // Get detailed transaction breakdown with error handling
            try {
        $successPayments = DB::table('payment_requests')
            ->where('userid', $userid)
            ->where('status', 1)
            ->selectRaw('SUM(amount) as total_amount, SUM(tax) as total_tax, SUM(amount - tax) as total_net, COUNT(*) as count')
            ->first();
            } catch (\Exception $e) {
                Log::error('Error fetching success payments', ['userid' => $userid, 'error' => $e->getMessage()]);
                $successPayments = (object)['total_amount' => 0, 'total_tax' => 0, 'total_net' => 0, 'count' => 0];
            }
        
            try {
        $settlementDetails = DB::table('transactions')
            ->where('userid', $userid)
            ->where('category', 'settlement')
            ->where('type', 'credit')
            ->select('id', 'amount', 'data3', 'data4', 'data5', 'created_at', 'data1 as description')
            ->orderBy('created_at', 'desc')
            ->get();
            } catch (\Exception $e) {
                Log::error('Error fetching settlements', ['userid' => $userid, 'error' => $e->getMessage()]);
                $settlementDetails = collect([]);
            }
        
            try {
        $adminDeductions = DB::table('transactions')
            ->where('userid', $userid)
            ->where('category', 'admin_deduction')
            ->where('type', 'debit')
            ->where(function($query) {
                $query->where('data1', 'payin')
                      ->orWhere('data2', 'payin');
            })
            ->select('id', 'amount', 'data1', 'created_at', 'description')
            ->orderBy('created_at', 'desc')
            ->get();
            } catch (\Exception $e) {
                Log::error('Error fetching admin deductions', ['userid' => $userid, 'error' => $e->getMessage()]);
                $adminDeductions = collect([]);
            }
        
            try {
        $otherDebits = DB::table('transactions')
            ->where('userid', $userid)
            ->where('type', 'debit')
            ->where(function($query) {
                $query->where('data1', 'payin')
                      ->orWhere('data2', 'payin');
            })
            ->where('category', '!=', 'settlement')
            ->where('category', '!=', 'admin_deduction')
            ->select('id', 'category', 'amount', 'data1', 'data2', 'created_at', 'description')
            ->orderBy('created_at', 'desc')
            ->get();
            } catch (\Exception $e) {
                Log::error('Error fetching other debits', ['userid' => $userid, 'error' => $e->getMessage()]);
                $otherDebits = collect([]);
            }
        
        return response()->json([
            'status' => true,
            'userid' => $userid,
            'summary' => $analysis,
            'breakdown' => [
                'successful_payments' => [
                    'total_amount' => (float) ($successPayments->total_amount ?? 0),
                    'total_tax' => (float) ($successPayments->total_tax ?? 0),
                    'total_net' => (float) ($successPayments->total_net ?? 0),
                    'count' => (int) ($successPayments->count ?? 0)
                ],
                'settlements' => [
                        'total' => $analysis['settled_amount'] ?? 0,
                        'count' => $settlementDetails->count(),
                        'details' => $settlementDetails->map(function($item) {
                            return [
                                'id' => $item->id,
                                'amount' => (float) ($item->amount ?? 0),
                                'data3' => (float) ($item->data3 ?? 0),
                                'data4' => (float) ($item->data4 ?? 0),
                                'data5' => (float) ($item->data5 ?? 0),
                                'created_at' => $item->created_at,
                                'description' => $item->description ?? ''
                            ];
                        })->values()
                ],
                'admin_deductions' => [
                    'total' => $analysis['other_debits'] ?? 0,
                        'count' => $adminDeductions->count(),
                        'details' => $adminDeductions->map(function($item) {
                            return [
                                'id' => $item->id,
                                'amount' => (float) ($item->amount ?? 0),
                                'data1' => $item->data1 ?? '',
                                'created_at' => $item->created_at,
                                'description' => $item->description ?? ''
                            ];
                        })->values()
                ],
                'other_debits' => [
                        'count' => $otherDebits->count(),
                        'details' => $otherDebits->map(function($item) {
                            return [
                                'id' => $item->id,
                                'category' => $item->category ?? '',
                                'amount' => (float) ($item->amount ?? 0),
                                'data1' => $item->data1 ?? '',
                                'data2' => $item->data2 ?? '',
                                'created_at' => $item->created_at,
                                'description' => $item->description ?? ''
                            ];
                        })->values()
                ]
            ],
            'calculation' => [
                    'calculated_payin' => $analysis['calculated_payin'] ?? 0,
                    'minus_settlements' => $analysis['settled_amount'] ?? 0,
                'minus_other_debits' => $analysis['other_debits'] ?? 0,
                    'equals_expected_payin' => $analysis['expected_payin'] ?? 0,
                    'current_payin' => $analysis['current_payin'] ?? 0,
                    'difference' => $analysis['difference'] ?? 0
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Diagnostic Error for userid: ' . $userid, [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Ensure we return JSON even if response() helper fails
            try {
                return response()->json([
                    'status' => false,
                    'error' => 'An error occurred while generating diagnostic',
                    'message' => $e->getMessage(),
                    'userid' => $userid ?? 'unknown'
                ], 500);
            } catch (\Exception $responseError) {
                // Last resort: return raw JSON
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => false,
                    'error' => 'An error occurred while generating diagnostic',
                    'message' => $e->getMessage(),
                    'userid' => $userid ?? 'unknown'
                ]);
                exit;
            }
        }
    }

    /**
     * Generate reconciliation report
     */
    public function generateReport(Request $request)
    {
        $userid = $request->input('userid');
        
        if ($userid) {
            $analysis = $this->analyzeUserWallet($userid);
            
            // Get transaction details
            $successTxns = Payment_request::where('userid', $userid)
                ->where('status', 1)
                ->count();
                
            $pendingTxns = Payment_request::where('userid', $userid)
                ->where('status', 0)
                ->count();
                
            $failedTxns = Payment_request::where('userid', $userid)
                ->where('status', 2)
                ->count();

            $analysis['transaction_counts'] = [
                'success' => $successTxns,
                'pending' => $pendingTxns,
                'failed' => $failedTxns
            ];

            return response()->json($analysis);
        }

        // Generate report for all users with issues (EXCLUDE ADMINS)
        $users = DB::table('users')
            ->where('role', 'user') // ONLY merchants/users, NO admins
            ->where('status', 1)
            ->get();

        $report = [];
        
        foreach ($users as $user) {
            $analysis = $this->analyzeUserWallet($user->userid);
            if ($analysis['has_issue']) {
                $report[] = $analysis;
            }
        }

        return response()->json([
            'total_users' => count($users),
            'users_with_issues' => count($report),
            'issues' => $report
        ]);
    }

    /**
     * Check UnitPayGo pending transactions
     */
    private function checkUnitPayGoPending($userid)
    {
        $pendingTxns = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subMinutes(10))
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        $checked = 0;
        $updated = 0;
        $failed = 0;
        $results = [];

        foreach ($pendingTxns as $txn) {
            $checked++;
            
            // UnitPayGo status check API
            $status = $this->checkUnitPayGoStatus($txn->transaction_id);
            
            if ($status['status'] === 'success') {
                $netAmount = $txn->amount - $txn->tax;
                addwallet($userid, $netAmount, '+', 'payin');
                
                Payment_request::where('id', $txn->id)->update([
                    'status' => 1,
                    'data1' => $status['utr'] ?? '00'
                ]);
                
                $updated++;
                $results[] = [
                    'txn_id' => $txn->transaction_id,
                    'status' => 'updated_to_success',
                    'amount' => $netAmount
                ];
            } elseif ($status['status'] === 'failed') {
                Payment_request::where('id', $txn->id)->update(['status' => 2]);
                $failed++;
                $results[] = ['txn_id' => $txn->transaction_id, 'status' => 'marked_failed'];
            }
        }

        return response()->json([
            'status' => true,
            'gateway' => 'UnitPayGo',
            'checked' => $checked,
            'updated' => $updated,
            'failed' => $failed,
            'results' => $results
        ]);
    }

    /**
     * Check FinQunes pending transactions
     */
    private function checkFinQunesPending($userid)
    {
        $pendingTxns = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subMinutes(10))
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        $checked = 0;
        $updated = 0;
        $failed = 0;
        $results = [];

        // Get FinQunes access token
        $finqunesController = new \App\Http\Controllers\Gateway\FinQunes(new \App\Http\Controllers\Logics_building(new \App\Http\Controllers\Gateway\PayinFunction()));
        $reflection = new \ReflectionClass($finqunesController);
        $method = $reflection->getMethod('getAccessToken');
        $method->setAccessible(true);
        $accessToken = $method->invoke($finqunesController);

        if (!$accessToken) {
            return response()->json(['status' => false, 'message' => 'Failed to get FinQunes token']);
        }

        foreach ($pendingTxns as $txn) {
            $checked++;
            
            $status = $this->checkFinQunesStatus($txn->transaction_id, $accessToken);
            
            if ($status['status'] === 'success') {
                $netAmount = $txn->amount - $txn->tax;
                addwallet($userid, $netAmount, '+', 'payin');
                
                Payment_request::where('id', $txn->id)->update([
                    'status' => 1,
                    'data4' => $status['utr'] ?? '00'
                ]);
                
                $updated++;
                $results[] = ['txn_id' => $txn->transaction_id, 'status' => 'updated_to_success', 'amount' => $netAmount];
            } elseif ($status['status'] === 'failed') {
                Payment_request::where('id', $txn->id)->update(['status' => 2]);
                $failed++;
                $results[] = ['txn_id' => $txn->transaction_id, 'status' => 'marked_failed'];
            }
        }

        return response()->json([
            'status' => true,
            'gateway' => 'FinQunes',
            'checked' => $checked,
            'updated' => $updated,
            'failed' => $failed,
            'results' => $results
        ]);
    }

    /**
     * Check PayU pending transactions
     */
    private function checkPayUPending($userid)
    {
        // PayU doesn't have public status check API
        return response()->json([
            'status' => false,
            'message' => 'PayU does not support status check API'
        ]);
    }

    /**
     * UnitPayGo Status Check
     */
    private function checkUnitPayGoStatus($transactionId)
    {
        try {
            $response = Http::post('https://pay.unitpaygo.com/api/v/payment/status', [
                'token' => 'your_unitpaygo_token', // Replace with actual token
                'txnid' => $transactionId
            ]);

            $data = $response->json();
            $status = $data['status'] ?? $data['statuscode'] ?? null;
            $utr = $data['utr'] ?? $data['refno'] ?? '00';

            if ($status === 'success' || $status === 'TXN') {
                return ['status' => 'success', 'utr' => $utr];
            } elseif (strtolower($status) === 'failed') {
                return ['status' => 'failed'];
            }

            return ['status' => 'pending'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * FinQunes Status Check
     */
    private function checkFinQunesStatus($transactionId, $accessToken)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://admin.finuniques.in/api/v1.1/t1/UpiIntent/status',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => ['reference' => $transactionId],
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $data = json_decode($response);
            
            if (isset($data->status) && $data->status == 'success' && isset($data->data)) {
                $status = strtolower($data->data->status ?? '');
                $utr = $data->data->utr ?? '00';
                
                if ($status === 'success') {
                    return ['status' => 'success', 'utr' => $utr];
                } elseif ($status === 'failed') {
                    return ['status' => 'failed'];
                }
            }

            return ['status' => 'pending'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
