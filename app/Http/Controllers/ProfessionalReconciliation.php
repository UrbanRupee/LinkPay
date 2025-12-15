<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use App\Models\Logs;

/**
 * Professional Payout Reconciliation Controller
 * 
 * RULES:
 * 1. NEVER refund without gateway confirmation
 * 2. ONLY mark failed if gateway confirms failure
 * 3. KEEP pending if gateway hasn't confirmed
 * 4. MANUAL intervention required for unknown statuses
 */
class ProfessionalReconciliation extends Controller
{
    // Minimum age before reconciliation (prevent premature marking)
    const MIN_AGE_MINUTES = 15;
    
    // Age after which manual review required
    const MANUAL_REVIEW_HOURS = 24;

    /**
     * Three-tier reconciliation system
     */
    public function reconcilePayout($payoutId)
    {
        $payout = PayoutRequest::find($payoutId);
        
        if (!$payout || $payout->status != 0) {
            return ['status' => 'skip', 'reason' => 'Not pending'];
        }

        // Check if payout was actually sent to gateway
        $wasSentToGateway = $this->verifyPayoutWasSent($payout);
        
        if (!$wasSentToGateway) {
            // TIER 1: Never sent to gateway - Safe to cancel
            return $this->handleNeverSentPayout($payout);
        }

        // TIER 2: Sent to gateway - Query gateway for status
        $gatewayStatus = $this->queryGatewayStatus($payout);
        
        if ($gatewayStatus['status'] === 'success') {
            return $this->handleSuccessfulPayout($payout, $gatewayStatus['utr']);
        }
        
        if ($gatewayStatus['status'] === 'failed') {
            // ONLY refund if gateway confirms failure
            return $this->handleFailedPayout($payout, $gatewayStatus['utr'] ?? '00', 'Gateway confirmed failure');
        }

        // TIER 3: Still pending at gateway
        $ageHours = now()->diffInHours($payout->created_at);
        
        if ($ageHours > self::MANUAL_REVIEW_HOURS) {
            // Flag for manual review - DO NOT auto-refund
            return ['status' => 'manual_review_required', 'age_hours' => $ageHours];
        }

        // Keep pending - wait for gateway confirmation
        return ['status' => 'pending', 'message' => 'Awaiting gateway confirmation'];
    }

    /**
     * Verify if payout was actually sent to gateway
     * Check logs table for API request
     */
    private function verifyPayoutWasSent($payout)
    {
        $user = DB::table('users')->where('userid', $payout->userid)->first();
        $gateway = $user ? $user->payoutgateway : null;

        // Check for gateway API request logs
        $gatewayNames = [
            '10' => 'Rudrax',
            '12' => 'FinQunes',
            '15' => 'Paydeer',
            '24' => 'UnitPayGo',
            '26' => 'NSO',
        ];

        $gatewayName = $gatewayNames[$gateway] ?? 'Unknown';

        // Check if request was logged
        $apiLog = Logs::where('uniqueid', 'LIKE', '%' . $gatewayName . 'Payout%')
            ->where('uniqueid', 'LIKE', '%' . $payout->transaction_id . '%')
            ->orWhere('value', 'LIKE', '%' . $payout->transaction_id . '%')
            ->first();

        return $apiLog !== null;
    }

    /**
     * TIER 1: Handle payouts never sent to gateway
     * Safe to cancel - no bank transaction exists
     */
    private function handleNeverSentPayout($payout)
    {
        DB::beginTransaction();
        try {
            // Refund to wallet (safe - never left our system)
            $refundAmount = $payout->amount + $payout->tax;
            Wallet::where('userid', $payout->userid)->update([
                'payout' => DB::raw('payout + ' . $refundAmount)
            ]);

            // Mark as failed
            $payout->update([
                'status' => 2,
                'utr' => '00',
                'remark' => 'Never sent to gateway - Safe refund'
            ]);

            Log::info('Payout Safe Refund (Never Sent)', [
                'txn_id' => $payout->transaction_id,
                'amount' => $refundAmount,
                'userid' => $payout->userid
            ]);

            DB::commit();
            return ['status' => 'refunded_safe', 'reason' => 'Never sent to gateway'];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * TIER 2: Handle gateway-confirmed failed payouts
     * Only refund if gateway explicitly says it failed
     */
    private function handleFailedPayout($payout, $utr, $reason)
    {
        DB::beginTransaction();
        try {
            // Refund to wallet (gateway confirmed failure)
            $refundAmount = $payout->amount + $payout->tax;
            Wallet::where('userid', $payout->userid)->update([
                'payout' => DB::raw('payout + ' . $refundAmount)
            ]);

            // Mark as failed
            $payout->update([
                'status' => 2,
                'utr' => $utr,
                'remark' => 'Gateway confirmed: ' . $reason
            ]);

            Log::warning('Payout Failed (Gateway Confirmed)', [
                'txn_id' => $payout->transaction_id,
                'amount' => $refundAmount,
                'userid' => $payout->userid,
                'utr' => $utr,
                'reason' => $reason
            ]);

            DB::commit();
            return ['status' => 'refunded_confirmed', 'utr' => $utr];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle successful payouts
     */
    private function handleSuccessfulPayout($payout, $utr)
    {
        DB::beginTransaction();
        try {
            $payout->update([
                'status' => 1,
                'utr' => $utr,
                'remark' => 'Success - Auto-reconciled'
            ]);

            Log::info('Payout Success (Auto-Reconciled)', [
                'txn_id' => $payout->transaction_id,
                'amount' => $payout->amount,
                'userid' => $payout->userid,
                'utr' => $utr
            ]);

            DB::commit();
            return ['status' => 'success', 'utr' => $utr];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Query gateway for payout status
     */
    private function queryGatewayStatus($payout)
    {
        $user = DB::table('users')->where('userid', $payout->userid)->first();
        $gateway = $user ? $user->payoutgateway : null;

        switch ($gateway) {
            case '10': // RudraXPay
                return $this->checkRudraXPayStatus($payout);
            case '12': // FinQunes
                return $this->checkFinQunesPayoutStatus($payout);
            case '15': // Paydeer
                return ['status' => 'no_api', 'message' => 'Paydeer has no status API'];
            case '24': // UnitPayGo
                return $this->checkUnitPayGoPayoutStatus($payout);
            case '26': // NSO
                return $this->checkNSOPayoutStatus($payout);
            default:
                return ['status' => 'unknown_gateway'];
        }
    }

    /**
     * Gateway-specific status checks
     */
    private function checkRudraXPayStatus($payout)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://merchant.rudraxpay.com/api/payout/checkstatus',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'userid' => 'RXP10100',
                    'orderid' => $payout->transaction_id
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $data = json_decode($response);
            
            if (isset($data->status) && $data->status == 'success') {
                return ['status' => 'success', 'utr' => $data->utr ?? '00'];
            }
            if (isset($data->status) && $data->status == 'failed') {
                return ['status' => 'failed', 'utr' => '00'];
            }

            return ['status' => 'pending'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkFinQunesPayoutStatus($payout)
    {
        try {
            $finqunesController = new \App\Http\Controllers\Gateway\FinQunes(
                new \App\Http\Controllers\Logics_building(
                    new \App\Http\Controllers\Gateway\PayinFunction()
                )
            );
            
            $reflection = new \ReflectionClass($finqunesController);
            $method = $reflection->getMethod('getAccessToken');
            $method->setAccessible(true);
            $accessToken = $method->invoke($finqunesController);

            if (!$accessToken) {
                return ['status' => 'error', 'message' => 'Token failed'];
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://admin.finuniques.in/api/v1.1/t1/withdrawal/status',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => ['reference' => $payout->transaction_id],
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
                    return ['status' => 'failed', 'utr' => $utr];
                }
            }

            return ['status' => 'pending'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkNSOPayoutStatus($payout)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.odinpe.in/api/check-payout-status',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'client_id' => 'NSO100011',
                    'txn_id' => $payout->transaction_id
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $data = json_decode($response);
            
            if (isset($data->status) && strtolower($data->status) === 'success' && isset($data->data)) {
                $payoutStatus = strtolower($data->data->status ?? '');
                $utr = $data->data->utr ?? '00';
                
                if ($payoutStatus === 'success') {
                    return ['status' => 'success', 'utr' => $utr];
                } elseif ($payoutStatus === 'failed') {
                    return ['status' => 'failed', 'utr' => $utr];
                }
            }

            return ['status' => 'pending'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkUnitPayGoPayoutStatus($payout)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::post('https://pay.unitpaygo.com/api/v/payout/v2/status', [
                'token' => 'your_unitpaygo_token',
                'apitxnid' => $payout->transaction_id
            ]);

            $data = $response->json();
            $status = $data['status'] ?? $data['statuscode'] ?? null;
            $utr = $data['utr'] ?? $data['refno'] ?? '00';

            if ($status === 'success' || $status === 'TXN') {
                return ['status' => 'success', 'utr' => $utr];
            } elseif (strtolower($status) === 'failed') {
                return ['status' => 'failed', 'utr' => $utr];
            }

            return ['status' => 'pending'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Professional bulk reconciliation
     */
    public function professionalBulkReconcile(Request $request)
    {
        $minAge = $request->input('min_age_minutes', self::MIN_AGE_MINUTES);
        $maxAge = $request->input('max_age_hours', self::MANUAL_REVIEW_HOURS);
        $limit = $request->input('limit', 50);

        // Get pending payouts within safe age range
        $payouts = PayoutRequest::where('status', 0)
            ->where('created_at', '>=', now()->subHours($maxAge))
            ->where('created_at', '<=', now()->subMinutes($minAge))
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();

        $results = [
            'total_checked' => 0,
            'success' => 0,
            'failed' => 0,
            'pending' => 0,
            'never_sent' => 0,
            'manual_review' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($payouts as $payout) {
            $results['total_checked']++;
            
            $result = $this->reconcilePayout($payout->id);
            
            switch ($result['status']) {
                case 'success':
                    $results['success']++;
                    break;
                case 'refunded_confirmed':
                case 'failed':
                    $results['failed']++;
                    break;
                case 'refunded_safe':
                case 'never_sent':
                    $results['never_sent']++;
                    break;
                case 'manual_review_required':
                    $results['manual_review']++;
                    break;
                case 'pending':
                    $results['pending']++;
                    break;
                default:
                    $results['errors']++;
            }

            $results['details'][] = [
                'txn_id' => $payout->transaction_id,
                'userid' => $payout->userid,
                'amount' => $payout->amount,
                'result' => $result['status'],
                'message' => $result['reason'] ?? $result['message'] ?? ''
            ];
        }

        // Log the reconciliation
        Logs::create([
            'uniqueid' => 'ProfessionalRecon_' . now()->timestamp,
            'value' => json_encode($results),
            'data1' => 'professional_reconciliation'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Professional reconciliation completed',
            'results' => $results
        ]);
    }

    /**
     * Get payouts requiring manual review
     */
    public function getManualReviewQueue()
    {
        $oldPayouts = PayoutRequest::where('payout_requests.status', 0)
            ->where('payout_requests.created_at', '<', now()->subHours(self::MANUAL_REVIEW_HOURS))
            ->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')
            ->select('payout_requests.*', 'users.payoutgateway', 'users.name')
            ->orderBy('payout_requests.id', 'ASC')
            ->get()
            ->map(function($payout) {
                return [
                    'id' => $payout->id,
                    'transaction_id' => $payout->transaction_id,
                    'userid' => $payout->userid,
                    'name' => $payout->name,
                    'amount' => $payout->amount,
                    'tax' => $payout->tax,
                    'total' => $payout->amount + $payout->tax,
                    'gateway' => gateway_name($payout->payoutgateway, 'payout'),
                    'age_hours' => now()->diffInHours($payout->created_at),
                    'created_at' => $payout->created_at
                ];
            });

        return response()->json([
            'status' => true,
            'count' => count($oldPayouts),
            'queue' => $oldPayouts
        ]);
    }

    /**
     * Manual review action - admin confirms status
     */
    public function manualReviewAction(Request $request, $payoutId)
    {
        $action = $request->input('action'); // 'success', 'failed', 'keep_pending'
        $utr = $request->input('utr', '00');
        $reason = $request->input('reason', 'Manual review by admin');

        $payout = PayoutRequest::find($payoutId);
        
        if (!$payout) {
            return response()->json(['status' => false, 'message' => 'Payout not found'], 404);
        }

        DB::beginTransaction();
        try {
            if ($action === 'success') {
                $payout->update([
                    'status' => 1,
                    'utr' => $utr,
                    'remark' => 'Manual confirmation: ' . $reason
                ]);
                
                Log::info('Manual Payout Success', [
                    'txn_id' => $payout->transaction_id,
                    'admin_action' => true,
                    'utr' => $utr
                ]);

            } elseif ($action === 'failed') {
                // Refund
                $refundAmount = $payout->amount + $payout->tax;
                Wallet::where('userid', $payout->userid)->update([
                    'payout' => DB::raw('payout + ' . $refundAmount)
                ]);

                $payout->update([
                    'status' => 2,
                    'utr' => $utr,
                    'remark' => 'Manual confirmation: ' . $reason
                ]);

                Log::warning('Manual Payout Failure', [
                    'txn_id' => $payout->transaction_id,
                    'admin_action' => true,
                    'refunded' => $refundAmount
                ]);

            } else {
                // Keep pending - no action
                return response()->json([
                    'status' => true,
                    'message' => 'Payout kept pending for further review'
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Payout marked as {$action}",
                'action' => $action
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}


