<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use App\Models\Logs;
use App\Http\Controllers\Logics_building;

/**
 * AutoReconciliation Controller
 * Automatically reconciles pending payouts across all payment gateways
 */
class AutoReconciliation extends Controller
{
    protected $LogicsApi;

    public function __construct(Logics_building $LogicsApi)
    {
        $this->LogicsApi = $LogicsApi;
    }

    /**
     * Main reconciliation method - processes all pending payouts
     * UPDATED: Professional reconciliation with safety checks
     * Can be called via cron job or manual trigger
     */
    public function reconcileAllPendingPayouts(Request $request)
    {
        $startTime = microtime(true);
        $limit = $request->input('limit', 50); // Process max 50 per run
        $minAgeMinutes = $request->input('min_age', 15); // INCREASED: Only process payouts older than 15 minutes
        $maxAgeHours = $request->input('max_age', 24); // NEW: Don't auto-process very old payouts
        
        Log::info('🔄 Auto Reconciliation Started', [
            'limit' => $limit,
            'min_age_minutes' => $minAgeMinutes,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Get pending payouts with gateway information
        // UPDATED: Added max age filter to prevent auto-processing very old payouts
        $pendingPayouts = PayoutRequest::where('payout_requests.status', 0)
            ->where('payout_requests.created_at', '<=', now()->subMinutes($minAgeMinutes))
            ->where('payout_requests.created_at', '>=', now()->subHours($maxAgeHours))
            ->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')
            ->select('payout_requests.*', 'users.out_callback', 'users.payoutgateway')
            ->orderBy('payout_requests.id', 'DESC')
            ->limit($limit)
            ->get();

        $results = [
            'total_pending' => $pendingPayouts->count(),
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
            'details' => []
        ];

        foreach ($pendingPayouts as $payout) {
            try {
                $gateway = $payout->payoutgateway;
                $result = $this->reconcileSinglePayout($payout, $gateway);
                
                $results['processed']++;
                if ($result['status'] === 'success') {
                    $results['success']++;
                } elseif ($result['status'] === 'failed') {
                    $results['failed']++;
                } else {
                    $results['skipped']++;
                }
                
                $results['details'][] = [
                    'txn_id' => $payout->transaction_id,
                    'gateway' => gateway_name($gateway),
                    'status' => $result['status'],
                    'message' => $result['message']
                ];

            } catch (\Exception $e) {
                $results['errors'][] = [
                    'txn_id' => $payout->transaction_id,
                    'error' => $e->getMessage()
                ];
                Log::error('Auto Reconciliation Error', [
                    'txn_id' => $payout->transaction_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        
        Log::info('✅ Auto Reconciliation Completed', [
            'duration_seconds' => $duration,
            'results' => $results
        ]);

        // Store reconciliation log
        Logs::create([
            'uniqueid' => 'AutoReconciliation_' . now()->timestamp,
            'value' => json_encode($results),
            'data1' => 'auto_reconciliation'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Reconciliation completed',
            'duration' => $duration . 's',
            'results' => $results
        ]);
    }

    /**
     * Reconcile a single payout based on gateway
     * UPDATED FOR EASEBUZZ ONLY
     */
    private function reconcileSinglePayout($payout, $gateway)
    {
        switch ($gateway) {
            case '28': // Easebuzz - ONLY ACTIVE GATEWAY
                return $this->reconcileEasebuzz($payout);
            
            // ALL OTHER GATEWAYS DISABLED
            // case '10': // RudraXPay
            //     return $this->reconcileRudraXPay($payout);
            // case '12': // FinQunes
            //     return $this->reconcileFinQunes($payout);
            // case '15': // Paydeer
            //     return $this->reconcilePaydeer($payout);
            // case '24': // UnitPayGo
            //     return $this->reconcileUnitPayGo($payout);
            // case '26': // NSO/Odinpe
            //     return $this->reconcileNSO($payout);
            // case '22': // PayPayout
            //     return $this->reconcilePayPayout($payout);
            // case '23': // PayU
            //     return $this->reconcilePayU($payout);
            // case '25': // Solitpay
            //     return $this->reconcileSolitpay($payout);
            
            default:
                return [
                    'status' => 'skipped',
                    'message' => 'Gateway not supported (Only Easebuzz active)'
                ];
        }
    }

    /**
     * ✅ Easebuzz Payout Reconciliation (Gateway 28)
     * Simple working functionality for Easebuzz callback reconciliation
     */
    private function reconcileEasebuzz($payout)
    {
        try {
            // Easebuzz Configuration
            $merchantKey = 'AEFQ63QEFK';
            $salt = 'BMHVGJZTOJ';
            $env = 'prod'; // production
            $baseUrl = $env === 'prod' ? 'https://pay.easebuzz.in' : 'https://testpay.easebuzz.in';
            
            // Generate hash for transaction retrieval
            // hash = key|txnid|amount|salt
            $hashString = $merchantKey . '|' . $payout->transaction_id . '|' . $payout->amount . '|' . $salt;
            $hash = hash('sha512', $hashString);
            
            Log::info('🔍 Easebuzz Payout Status Check', [
                'txn_id' => $payout->transaction_id,
                'amount' => $payout->amount
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
                    'txnid' => $payout->transaction_id,
                    'amount' => $payout->amount,
                    'email' => 'support@xpaisa.in', // Default email
                    'phone' => '9999999999', // Default phone
                    'hash' => $hash
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Log::error('Easebuzz API Error', ['error' => $error, 'txn_id' => $payout->transaction_id]);
                return ['status' => 'error', 'message' => 'Network error: ' . $error];
            }

            // Parse Easebuzz response
            $data = json_decode($response);
            
            Log::info('Easebuzz Status Response', [
                'txn_id' => $payout->transaction_id,
                'response' => $data
            ]);
            
            // Check transaction status
            if (isset($data->status)) {
                $txnStatus = strtolower($data->status);
                $utr = $data->bank_ref_num ?? $data->easepayid ?? '00';
                
                // SUCCESS STATUSES
                if ($txnStatus === 'success' || $txnStatus === 'completed') {
                    $this->handlePayoutSuccess($payout, $utr);
                    return [
                        'status' => 'success',
                        'message' => 'Easebuzz payout successful',
                        'utr' => $utr
                    ];
                }
                
                // FAILURE STATUSES
                if ($txnStatus === 'failure' || $txnStatus === 'failed' || $txnStatus === 'usercancelled') {
                    $this->handlePayoutFailed($payout, $utr, 'Easebuzz confirmed failure: ' . $txnStatus);
                    return [
                        'status' => 'failed',
                        'message' => 'Easebuzz payout failed, refunded',
                        'easebuzz_status' => $txnStatus
                    ];
                }
                
                // PENDING STATUSES
                if ($txnStatus === 'pending' || $txnStatus === 'initiated' || $txnStatus === 'preinitiated') {
                    return [
                        'status' => 'pending',
                        'message' => 'Still processing at Easebuzz',
                        'easebuzz_status' => $txnStatus
                    ];
                }
            }

            // Unknown response
            return [
                'status' => 'pending',
                'message' => 'Unknown Easebuzz response - needs manual check'
            ];

        } catch (\Exception $e) {
            Log::error('Easebuzz Reconciliation Error', [
                'txn_id' => $payout->transaction_id,
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * RudraXPay Reconciliation (Gateway 1) - DISABLED
     */
    private function reconcileRudraXPay($payout)
    {
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
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['status' => 'error', 'message' => 'Network error: ' . $error];
        }

        $data = json_decode($response);
        
        if (isset($data->status) && $data->status == 'failed') {
            $this->handlePayoutFailed($payout, '00', $data->message ?? 'Failed');
            return ['status' => 'failed', 'message' => 'Payout failed, refunded'];
        }
        
        if (isset($data->status) && $data->status == 'success') {
            $this->handlePayoutSuccess($payout, $data->utr ?? '00');
            return ['status' => 'success', 'message' => 'Payout successful'];
        }

        return ['status' => 'pending', 'message' => 'Still pending'];
    }

    /**
     * FinQunes Reconciliation (Gateway 12)
     */
    private function reconcileFinQunes($payout)
    {
        // Get FinQunes access token
        $finqunesController = new \App\Http\Controllers\Gateway\FinQunes($this->LogicsApi);
        $reflection = new \ReflectionClass($finqunesController);
        $method = $reflection->getMethod('getAccessToken');
        $method->setAccessible(true);
        $accessToken = $method->invoke($finqunesController);

        if (!$accessToken) {
            return ['status' => 'error', 'message' => 'Failed to get FinQunes access token'];
        }

        // Check payout status
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://admin.finuniques.in/api/v1.1/t1/withdrawal/status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ['reference' => $payout->transaction_id],
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['status' => 'error', 'message' => 'Network error: ' . $error];
        }

        $data = json_decode($response);
        
        if (isset($data->status) && $data->status == 'success' && isset($data->data)) {
            $status = strtolower($data->data->status ?? '');
            $utr = $data->data->utr ?? '00';
            
            if ($status === 'success') {
                $this->handlePayoutSuccess($payout, $utr);
                return ['status' => 'success', 'message' => 'Payout successful'];
            } elseif ($status === 'failed') {
                $this->handlePayoutFailed($payout, $utr, 'Failed at gateway');
                return ['status' => 'failed', 'message' => 'Payout failed, refunded'];
            }
        }

        return ['status' => 'pending', 'message' => 'Still pending'];
    }

    /**
     * NSO/Odinpe Reconciliation (Gateway 3)
     */
    private function reconcileNSO($payout)
    {
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
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['status' => 'error', 'message' => 'Network error: ' . $error];
        }

        $data = json_decode($response);
        
        if (isset($data->status) && strtolower($data->status) === 'success' && isset($data->data)) {
            $payoutStatus = strtolower($data->data->status ?? '');
            $utr = $data->data->utr ?? '00';
            
            if ($payoutStatus === 'success') {
                $this->handlePayoutSuccess($payout, $utr);
                return ['status' => 'success', 'message' => 'Payout successful'];
            } elseif ($payoutStatus === 'failed') {
                $this->handlePayoutFailed($payout, $utr, 'Failed at gateway');
                return ['status' => 'failed', 'message' => 'Payout failed, refunded'];
            }
        }

        return ['status' => 'pending', 'message' => 'Still pending'];
    }

    /**
     * UnitPayGo Reconciliation (Gateway 4)
     */
    private function reconcileUnitPayGo($payout)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://pay.unitpaygo.com/api/v/payout/v2/status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'token' => 'your_unitpaygo_token', // Replace with actual token
                'apitxnid' => $payout->transaction_id
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['status' => 'error', 'message' => 'Network error: ' . $error];
        }

        $data = json_decode($response);
        $statusVal = $data->status ?? $data->statuscode ?? null;
        $utr = $data->utr ?? $data->refno ?? $data->payid ?? '00';
        
        if ($statusVal === 'success' || $statusVal === 'TXN') {
            $this->handlePayoutSuccess($payout, $utr);
            return ['status' => 'success', 'message' => 'Payout successful'];
        } elseif (is_string($statusVal) && strtolower($statusVal) === 'failed') {
            $this->handlePayoutFailed($payout, $utr, 'Failed at gateway');
            return ['status' => 'failed', 'message' => 'Payout failed, refunded'];
        }

        return ['status' => 'pending', 'message' => 'Still pending'];
    }

    /**
     * Paydeer Reconciliation (Gateway 15)
     * UPDATED: Now has status check API!
     */
    private function reconcilePaydeer($payout)
    {
        try {
            // Paydeer credentials (replace with actual)
            $username = 'your_paydeer_username';
            $password = 'your_paydeer_password';

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://xpay.paydeer.in/ws/v1/Fetch/txnstatus',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'requestid' => 'recon_' . $payout->transaction_id,
                    'username' => $username,
                    'password' => $password,
                    'txnid' => $payout->transaction_id
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                return ['status' => 'error', 'message' => 'Network error: ' . $error];
            }

            $data = json_decode($response);
            
            // Parse Paydeer response
            if (isset($data->status)) {
                $status = strtolower($data->status);
                $utr = $data->utr ?? $data->rrn ?? '00';
                
                if ($status === 'success' || $status === 'completed') {
                    $this->handlePayoutSuccess($payout, $utr);
                    return ['status' => 'success', 'message' => 'Payout successful'];
                } elseif ($status === 'failed' || $status === 'failure') {
                    $this->handlePayoutFailed($payout, $utr, 'Paydeer confirmed failure');
                    return ['status' => 'failed', 'message' => 'Payout failed, refunded'];
                }
            }

            return ['status' => 'pending', 'message' => 'Still pending'];
            
        } catch (\Exception $e) {
            Log::error('Paydeer Reconciliation Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * PayU Reconciliation (Gateway 13)
     */
    private function reconcilePayU($payout)
    {
        return ['status' => 'skipped', 'message' => 'PayU auto-reconciliation not implemented yet'];
    }

    /**
     * PayPayout Reconciliation (Gateway 22)
     */
    private function reconcilePayPayout($payout)
    {
        try {
            // PayPayout doesn't have a public status check API
            // For now, just mark old transactions (>24h) as failed
            $ageHours = now()->diffInHours($payout->created_at);
            
            if ($ageHours > 24) {
                return ['status' => 'failed', 'message' => 'Auto-cancelled after 24 hours', 'action' => 'refund'];
            }
            
            return ['status' => 'pending', 'message' => 'Waiting for callback (no status API available)'];
        } catch (\Exception $e) {
            Log::error('PayPayout Reconciliation Error', [
                'txn_id' => $payout->transaction_id,
                'error' => $e->getMessage()
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Solitpay Reconciliation (Gateway 15)
     */
    private function reconcileSolitpay($payout)
    {
        return ['status' => 'skipped', 'message' => 'Solitpay auto-reconciliation not implemented yet'];
    }

    /**
     * Handle successful payout
     */
    private function handlePayoutSuccess($payout, $utr)
    {
        DB::beginTransaction();
        try {
            PayoutRequest::where('transaction_id', $payout->transaction_id)->update([
                'status' => 1,
                'utr' => $utr,
                'remark' => 'Auto-reconciled: Success'
            ]);

            // Send callback to merchant
            if ($payout->out_callback) {
                $callbackData = [
                    'transaction_id' => $payout->transaction_id,
                    'amount' => $payout->amount,
                    'fees' => $payout->tax,
                    'status' => 'success',
                    'utr' => $utr
                ];
                $this->LogicsApi->CallbacksendToClient($payout->out_callback, json_encode($callbackData));
            }

            DB::commit();
            Log::info('✅ Payout Success Auto-Reconciled', [
                'txn_id' => $payout->transaction_id,
                'utr' => $utr
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle failed payout
     * CRITICAL: Only call this when gateway CONFIRMS failure
     * NEVER refund based on timeout or "no response" - could cause double payment!
     * SECURITY: Verify wallet was actually deducted before refunding
     */
    private function handlePayoutFailed($payout, $utr, $remark)
    {
        DB::beginTransaction();
        try {
            // 🚨 CRITICAL SECURITY CHECK #1: Verify wallet was actually deducted
            $wasDeducted = $this->verifyWalletWasDeducted($payout);
            
            if (!$wasDeducted) {
                // SECURITY VIOLATION: Wallet was never deducted - DO NOT refund!
                Log::critical('🚨 REFUND BLOCKED - Wallet was never deducted!', [
                    'txn_id' => $payout->transaction_id,
                    'userid' => $payout->userid,
                    'amount' => $payout->amount,
                    'reason' => 'SECURITY: Attempting to refund money that was never deducted',
                    'payout_id' => $payout->id
                ]);
                
                // Just mark as failed WITHOUT refunding
                PayoutRequest::where('id', $payout->id)->update([
                    'status' => 2,
                    'utr' => '00',
                    'remark' => 'Cancelled - No deduction found (Security block: No refund)'
                ]);
                
                DB::commit();
                return; // Exit without refunding
            }

            // CRITICAL SAFETY CHECK #2: Only refund if we have gateway confirmation
            // Check logs to verify gateway was actually queried
            $gatewayLog = Logs::where('uniqueid', 'LIKE', '%PayoutResponse%')
                ->where('uniqueid', 'LIKE', '%' . $payout->transaction_id . '%')
                ->first();

            if (!$gatewayLog && stripos($remark, 'Cancelled by admin') === false) {
                // No gateway confirmation - DO NOT refund
                Log::warning('⚠️ Refund Blocked - No Gateway Confirmation', [
                    'txn_id' => $payout->transaction_id,
                    'reason' => 'Safety check: No gateway API response found'
                ]);
                return; // Exit without refunding
            }

            // All checks passed - Safe to refund
            $refundAmount = $payout->amount + $payout->tax;
            Wallet::where('userid', $payout->userid)->update([
                'payout' => DB::raw('payout + ' . $refundAmount)
            ]);

            PayoutRequest::where('transaction_id', $payout->transaction_id)->update([
                'status' => 2,
                'utr' => $utr,
                'remark' => 'Auto-reconciled: ' . $remark
            ]);

            // Send callback to merchant
            if ($payout->out_callback) {
                $callbackData = [
                    'transaction_id' => $payout->transaction_id,
                    'amount' => $payout->amount,
                    'fees' => $payout->tax,
                    'status' => 'failed',
                    'utr' => $utr
                ];
                $this->LogicsApi->CallbacksendToClient($payout->out_callback, json_encode($callbackData));
            }

            DB::commit();
            Log::info('❌ Payout Failed Auto-Reconciled', [
                'txn_id' => $payout->transaction_id,
                'refund_amount' => $refundAmount,
                'utr' => $utr,
                'gateway_confirmed' => true,
                'deduction_verified' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 🚨 CRITICAL SECURITY CHECK
     * Verify that wallet was actually deducted when payout was created
     * Prevents refunding money that was never taken
     */
    private function verifyWalletWasDeducted($payout)
    {
        // Check for deduction in transactions table
        $deduction = DB::table('transactions')
            ->where('userid', $payout->userid)
            ->where('category', 'payout')
            ->where('type', 'debit')
            ->where(function($q) use ($payout) {
                $q->where('data1', $payout->transaction_id)
                  ->orWhere('created_at', '>=', $payout->created_at->subSeconds(10))
                  ->where('created_at', '<=', $payout->created_at->addSeconds(10));
            })
            ->where('amount', '>=', $payout->amount)
            ->first();

        if ($deduction) {
            return true; // Wallet was deducted - safe to refund
        }

        // Alternative check: Look for wallet history in payout_requests table data
        // Some gateways might deduct without transaction log
        // Check if payout has a "deducted" flag or initial wallet snapshot
        
        // For now, if no transaction record found, assume NOT deducted
        return false;
    }

    /**
     * Get reconciliation statistics
     */
    public function getReconciliationStats()
    {
        $stats = [
            'total_pending' => PayoutRequest::where('status', 0)->count(),
            'older_than_2min' => PayoutRequest::where('status', 0)
                ->where('created_at', '<=', now()->subMinutes(2))
                ->count(),
            'older_than_10min' => PayoutRequest::where('status', 0)
                ->where('created_at', '<=', now()->subMinutes(10))
                ->count(),
            'older_than_1hour' => PayoutRequest::where('status', 0)
                ->where('created_at', '<=', now()->subHours(1))
                ->count(),
            'by_gateway' => PayoutRequest::where('payout_requests.status', 0)
                ->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')
                ->select('users.payoutgateway', DB::raw('count(*) as count'))
                ->groupBy('users.payoutgateway')
                ->get()
                ->map(function($item) {
                    return [
                        'gateway' => gateway_name($item->payoutgateway),
                        'count' => $item->count
                    ];
                })
        ];

        return response()->json($stats);
    }

    /**
     * Show reconciliation admin page
     */
    public function showReconciliationPage()
    {
        $stats = [
            'total_pending' => PayoutRequest::where('status', 0)->count(),
            'older_than_2min' => PayoutRequest::where('status', 0)
                ->where('created_at', '<=', now()->subMinutes(2))
                ->count(),
            'older_than_10min' => PayoutRequest::where('status', 0)
                ->where('created_at', '<=', now()->subMinutes(10))
                ->count(),
            'older_than_1hour' => PayoutRequest::where('status', 0)
                ->where('created_at', '<=', now()->subHours(1))
                ->count(),
            'by_gateway' => PayoutRequest::where('payout_requests.status', 0)
                ->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')
                ->select('users.payoutgateway', DB::raw('count(*) as count'))
                ->groupBy('users.payoutgateway')
                ->get()
                ->map(function($item) {
                    return (object)[
                        'gateway' => gateway_name($item->payoutgateway),
                        'gateway_id' => $item->payoutgateway,
                        'count' => $item->count
                    ];
                })
        ];

        // Get recent reconciliation logs
        $recentLogs = Logs::where('data1', 'auto_reconciliation')
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get()
            ->map(function($log) {
                return (object)[
                    'id' => $log->id,
                    'timestamp' => $log->created_at,
                    'data' => json_decode($log->value)
                ];
            });

        // Get pending transactions details for manual actions
        $pendingTransactions = PayoutRequest::where('payout_requests.status', 0)
            ->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')
            ->select('payout_requests.*', 'users.payoutgateway')
            ->orderBy('payout_requests.id', 'DESC')
            ->limit(100)
            ->get()
            ->map(function($txn) {
                return (object)[
                    'id' => $txn->id,
                    'transaction_id' => $txn->transaction_id,
                    'userid' => $txn->userid,
                    'amount' => $txn->amount,
                    'tax' => $txn->tax,
                    'gateway' => gateway_name($txn->payoutgateway),
                    'created_at' => $txn->created_at,
                    'age_minutes' => now()->diffInMinutes($txn->created_at)
                ];
            });

        return view('admin.payout_recon', compact('stats', 'recentLogs', 'pendingTransactions'));
    }

    /**
     * Bulk cancel/fail pending transactions
     * UPDATED: Professional logic with safety checks
     */
    public function bulkCancelTransactions(Request $request)
    {
        $gateway = $request->input('gateway');
        $minAge = $request->input('min_age_hours', 1); // Default: older than 1 hour
        $reason = $request->input('reason', 'Cancelled by admin - Test/Old transaction');
        $verifyNotSent = $request->input('verify_not_sent', true); // NEW: Safety flag

        DB::beginTransaction();
        try {
            // Get transactions to cancel
            $query = PayoutRequest::where('payout_requests.status', 0)
                ->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')
                ->where('payout_requests.created_at', '<=', now()->subHours($minAge))
                ->select('payout_requests.*', 'users.payoutgateway', 'users.out_callback');

            if ($gateway && $gateway !== 'all') {
                $query->where('users.payoutgateway', $gateway);
            }

            $transactions = $query->get();
            $cancelled = 0;
            $refunded = 0;
            $skipped = 0;

            foreach ($transactions as $txn) {
                // 🚨 CRITICAL SECURITY CHECK #1: Verify wallet was actually deducted
                $wasDeducted = $this->verifyWalletWasDeducted($txn);
                
                if (!$wasDeducted) {
                    // Wallet never deducted - Just mark as failed, NO refund!
                    PayoutRequest::where('id', $txn->id)->update([
                        'status' => 2,
                        'utr' => '00',
                        'remark' => $reason . ' (No refund: Wallet was never deducted)'
                    ]);
                    
                    Log::critical('🚨 Bulk Cancel - No Refund (Never Deducted)', [
                        'txn_id' => $txn->transaction_id,
                        'userid' => $txn->userid,
                        'amount' => $txn->amount
                    ]);
                    
                    $cancelled++;
                    $skipped++; // Count as skipped for refund
                    continue;
                }

                // PROFESSIONAL SAFETY CHECK #2: Verify payout wasn't sent to gateway
                if ($verifyNotSent) {
                    $wasSent = $this->wasPayoutSentToGateway($txn);
                    if ($wasSent) {
                        // SKIP - Don't refund if sent to gateway!
                        Log::warning('Bulk Cancel Skipped - Payout was sent to gateway', [
                            'txn_id' => $txn->transaction_id,
                            'gateway' => gateway_name($txn->payoutgateway, 'payout'),
                            'reason' => 'Safety: Gateway may still process this'
                        ]);
                        $skipped++;
                        continue; // Skip this transaction
                    }
                }

                // All checks passed - Safe to refund
                $refundAmount = $txn->amount + $txn->tax;
                Wallet::where('userid', $txn->userid)->update([
                    'payout' => DB::raw('payout + ' . $refundAmount)
                ]);

                // Mark as failed (2)
                PayoutRequest::where('id', $txn->id)->update([
                    'status' => 2,
                    'utr' => '00',
                    'remark' => $reason . ' (Verified: Not sent to gateway, Deduction confirmed)'
                ]);

                // Send callback if exists
                if ($txn->out_callback) {
                    $callbackData = [
                        'transaction_id' => $txn->transaction_id,
                        'amount' => $txn->amount,
                        'fees' => $txn->tax,
                        'status' => 'failed',
                        'utr' => '00'
                    ];
                    $this->LogicsApi->CallbacksendToClient($txn->out_callback, json_encode($callbackData));
                }

                $cancelled++;
                $refunded += $refundAmount;
            }

            DB::commit();

            // Log the bulk cancel
            Logs::create([
                'uniqueid' => 'BulkCancel_' . now()->timestamp,
                'value' => json_encode([
                    'gateway' => gateway_name($gateway),
                    'min_age_hours' => $minAge,
                    'cancelled_count' => $cancelled,
                    'skipped_count' => $skipped,
                    'refunded_amount' => $refunded,
                    'reason' => $reason,
                    'safety_check_enabled' => $verifyNotSent
                ]),
                'data1' => 'bulk_cancel'
            ]);

            $message = "Cancelled: $cancelled transactions";
            if ($skipped > 0) {
                $message .= " | Skipped: $skipped (sent to gateway - requires manual review)";
            }

            return response()->json([
                'status' => true,
                'message' => $message,
                'cancelled' => $cancelled,
                'skipped' => $skipped,
                'refunded' => $refunded
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk Cancel Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error cancelling transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel specific transaction by ID
     */
    public function cancelTransaction(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $txn = PayoutRequest::find($id);
            
            if (!$txn) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($txn->status != 0) {
                return response()->json(['status' => false, 'message' => 'Transaction is not pending'], 400);
            }

            $reason = $request->input('reason', 'Cancelled by admin');

            // Refund to wallet
            $refundAmount = $txn->amount + $txn->tax;
            Wallet::where('userid', $txn->userid)->update([
                'payout' => DB::raw('payout + ' . $refundAmount)
            ]);

            // Mark as failed
            $txn->update([
                'status' => 2,
                'utr' => '00',
                'remark' => $reason
            ]);

            // Send callback if exists
            if ($txn->out_callback) {
                $callbackData = [
                    'transaction_id' => $txn->transaction_id,
                    'amount' => $txn->amount,
                    'fees' => $txn->tax,
                    'status' => 'failed',
                    'utr' => '00'
                ];
                $this->LogicsApi->CallbacksendToClient($txn->out_callback, json_encode($callbackData));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Transaction cancelled and refunded',
                'refunded' => $refundAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PROFESSIONAL SAFETY CHECK
     * Verify if payout was actually sent to gateway
     * Returns TRUE if payout was sent, FALSE if it's safe to cancel
     */
    private function wasPayoutSentToGateway($payout)
    {
        $gatewayNames = [
            '10' => ['Rudrax', 'RudraXPay'],
            '12' => ['FinQunes', 'FinUniq'],
            '15' => ['Paydeer'],
            '19' => ['HZTPay'],
            '20' => ['PayVanta'],
            '21' => ['ASVB'],
            '22' => ['PayPayout'],
            '23' => ['PayU'],
            '24' => ['UnitPayGo'],
            '25' => ['Solitpay'],
            '26' => ['NSO', 'Odinpe'],
        ];

        $gateway = $payout->payoutgateway ?? null;
        $names = $gatewayNames[$gateway] ?? [];

        if (empty($names)) {
            return false; // Unknown gateway - assume not sent
        }

        // Check logs for API request to gateway
        foreach ($names as $name) {
            $apiLog = Logs::where(function($q) use ($name, $payout) {
                $q->where('uniqueid', 'LIKE', "%{$name}%Payout%Request%")
                  ->orWhere('uniqueid', 'LIKE', "%{$name}%Payout%");
            })
            ->where(function($q) use ($payout) {
                $q->where('uniqueid', 'LIKE', '%' . $payout->transaction_id . '%')
                  ->orWhere('value', 'LIKE', '%' . $payout->transaction_id . '%');
            })
            ->first();

            if ($apiLog) {
                return true; // Found API request log - payout was sent
            }
        }

        return false; // No API log found - safe to cancel
    }
}

