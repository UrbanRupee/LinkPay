<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Payment_request;
use App\Models\Logs;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class AuroPay extends Controller
{
    // API Configuration - HARDCODED
    const ENV = 'prod'; // 'uat' or 'prod'
    
    // API Credentials - HARDCODED (Production Environment)
    const ACCESS_KEY = '72D172B2CC6464717860480DE8FFB502';
    const SECRET_KEY = '35/uQLUkiImjfWvCWGA5OxnpZ2nM9G/cXWRwPOHOJWY=';
    
    // API URLs
    const UAT_BASE_URL = 'https://api.uat.auropay.net';
    const PROD_BASE_URL = 'https://secure-api.auropay.net';
    
    /**
     * Get base URL based on environment
     */
    private function getBaseUrl()
    {
        return self::ENV === 'prod' ? self::PROD_BASE_URL : self::UAT_BASE_URL;
    }
    
    /**
     * Get API credentials - HARDCODED
     */
    private function getCredentials()
    {
        return [
            'access_key' => self::ACCESS_KEY,
            'secret_key' => self::SECRET_KEY,
        ];
    }
    
    /**
     * Initiate PayIn with AuroPay
     * Supports both Payment Link and QR Code
     * 
     * @param Request $request
     * @param string $trn Transaction ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayin(Request $request, $trn)
    {
        try {
            // Get payment request details
            $pr = Payment_request::where('transaction_id', $trn)->first();
            if (!$pr) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }
            
            $credentials = $this->getCredentials();
            $baseUrl = $this->getBaseUrl();
            
            // Check if QR code is required (optional parameter)
            // If not specified, check payment_requests table or use default
            $qrRequired = $request->input('qr_required', false);
            
            // You can also check from payment_requests table if stored there
            // $qrRequired = $pr->qr_required ?? false;
            
            // Customer details - Split name into first and last
            $fullName = $pr->name ?? 'Customer User';
            $nameParts = explode(' ', trim($fullName), 2);
            $firstName = $nameParts[0] ?? 'Customer';
            $lastName = $nameParts[1] ?? 'User'; // AuroPay requires lastName
            
            $customer = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $pr->email ?? 'customer@dhankubera.com',
                'phone' => $pr->mobile ?? '9999999999',
            ];
            
            // Expiration date (24 hours from now)
            $expireOn = now()->addHours(24)->format('d-m-Y H:i:s');
            
            // Prepare common request data
            $requestData = [
                'title' => $trn,
                'amount' => (float)$pr->amount,
                'currency' => 'INR',
                'expireOn' => $expireOn,
                'invoiceNumber' => $trn,
                'enableMultiplePayment' => false,
                'Customers' => [$customer],
                'CallbackParameters' => [
                    'ReferenceNo' => $trn,
                    'ReferenceType' => 'Order',
                    'CallbackApiUrl' => url('/api/gateway/auropay/callback'),
                ],
                'shortDescription' => 'Payment for ' . $trn,
                'paymentDescription' => 'Payment transaction for order ' . $trn,
                'ResponseType' => 1, // Callback
                'Settings' => [
                    'displaySummary' => false,
                ],
            ];
            
            // Determine API endpoint based on QR requirement
            // QR Code API if qr_required=true, Payment Link API otherwise
            $apiEndpoint = $qrRequired ? '/api/paymentqrcodes' : '/api/paymentlinks';
            
            // Log the request
            $this->logRequest('AUROPAY_PAYIN_REQ', $requestData, $trn, $pr->userid);
            
            // Call AuroPay API - Force IPv4
            $response = Http::withHeaders([
                'x-version' => '1.0',
                'x-access-key' => $credentials['access_key'],
                'x-secret-key' => $credentials['secret_key'],
                'Content-Type' => 'application/json',
            ])
            ->withOptions([
                'force_ip_resolve' => 'v4', // Force IPv4 to match whitelisted IP
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ]
            ])
            ->timeout(30)->post($baseUrl . $apiEndpoint, $requestData);
            
            $responseData = $response->json();
            
            // Log the full response including errors
            $this->logRequest('AUROPAY_PAYIN_RES', [
                'status_code' => $response->status(),
                'body' => $responseData,
                'raw_body' => $response->body(),
                'endpoint' => $apiEndpoint
            ], $trn, $pr->userid);
            
            // Check response
            if (isset($responseData['id']) && isset($responseData['paymentLink'])) {
                // Success - Store AuroPay details
                $pr->data3 = $responseData['id']; // AuroPay payment ID
                // Reserve data4 for the eventual UTR. Do not store short links here.
                $pr->data4 = null;
                
                
                // Store QR code if available
                if (isset($responseData['qrCode']) && $responseData['qrCode']) {
                    $pr->data5 = 'QR_AVAILABLE'; // Flag to indicate QR is available
                    // You can store the QR code in a separate field if needed
                } else {
                    $pr->data5 = null;
                }
                
                $pr->save();
                
                // Prepare response
                $returnData = [
                    'status' => true,
                    'message' => 'Payment initiated successfully',
                    'url' => $responseData['paymentLink'],
                    'payment_url' => $responseData['paymentLink'],
                    'short_link' => $responseData['shortLink'] ?? $responseData['paymentLink'],
                    'amount' => $pr->amount,
                    'tax' => $pr->tax,
                    'auropay_id' => $responseData['id'],
                ];
                
                // Add QR code to response if available
                if (isset($responseData['qrCode']) && $responseData['qrCode']) {
                    $returnData['qr_code'] = $responseData['qrCode'];
                    $returnData['qr_available'] = true;
                }
                
                return response()->json($returnData);
            } else {
                // Error from AuroPay
                $errorMessage = $responseData['message'] ?? 'Payment initiation failed';
                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('AuroPay PayIn Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Handle AuroPay Callback (Supports GET & POST)
     */
    public function handleCallback(Request $request)
    {
        $pr = null;
        try {
            // AuroPay sends GET request with query parameters
            // GET: ?result=success&refNo=ORDER_ID&id=TRANSACTION_ID
            $data = $request->all();
            
            // Map GET parameters to expected format
            if ($request->has('refNo')) {
                $data['ReferenceNo'] = $request->input('refNo');
            }
            if ($request->has('id')) {
                $data['TransactionId'] = $request->input('id');
            }
            if ($request->has('result')) {
                $data['result'] = $request->input('result');
            }
            
            // Log callback
            $this->logRequest('AUROPAY_CALLBACK', $data, $data['ReferenceNo'] ?? 'unknown', null);
            
            // Get reference number (transaction ID)
            $txnId = $data['ReferenceNo'] ?? null;
            
            if (!$txnId) {
                Log::error('AuroPay Callback: Missing ReferenceNo');
                return response()->json(['status' => false, 'message' => 'Missing reference number']);
            }
            
            // Find payment request by transaction_id (order ID)
            $pr = Payment_request::where('transaction_id', $txnId)->first();
            
            // If not found by transaction_id, try to find by data1 (AuroPay transaction ID) to prevent duplicates
            if (!$pr) {
                $transactionId = $data['TransactionId'] ?? null;
                if ($transactionId) {
                    $pr = Payment_request::where('data1', $transactionId)->first();
                    if ($pr) {
                        Log::warning('AuroPay Callback: Found payment request by data1 instead of transaction_id', [
                            'txnId' => $txnId,
                            'data1' => $transactionId,
                            'existing_transaction_id' => $pr->transaction_id
                        ]);
                    }
                }
            }
            
            if (!$pr) {
                Log::error('AuroPay Callback: Transaction not found', [
                    'txnId' => $txnId,
                    'TransactionId' => $data['TransactionId'] ?? null
                ]);
                return $this->redirectToFailurePage(null, [
                    'txn' => $txnId,
                    'amount' => 0,
                    'reason' => 'Transaction not found'
                ]);
            }

            $this->enrichPaymentRequestPayload($pr, $data);
            
            // Check if already processed
            if ($pr->status != 0) {
                Log::info('AuroPay Callback: Already processed', ['txnId' => $txnId, 'status' => $pr->status]);
                
                // Redirect based on existing status
                if ($pr->status == 1) {
                    if ($pr->isDirty('callback_payload')) {
                        $pr->save();
                    }
                    return $this->redirectToSuccessPage($pr, [
                        'txn' => $txnId,
                        'amount' => $pr->amount,
                        'utr' => $pr->data4 ?: ($pr->data1 ?? 'processed'),
                        'status' => 'success',
                    ]);
                } else {
                    if ($pr->isDirty('callback_payload')) {
                        $pr->save();
                    }
                    return $this->redirectToFailurePage($pr, [
                        'reason' => 'Transaction already processed'
                    ]);
                }
            }
            
            // Get transaction status from AuroPay
            $transactionId = $data['TransactionId'] ?? null;
            
            // Check if this AuroPay transaction ID has already been processed in another Payment_request
            // This prevents duplicate entries if callback is called multiple times
            if ($transactionId) {
                $existingByData1 = Payment_request::where('data1', $transactionId)
                    ->where('id', '!=', $pr->id)
                    ->first();
                
                if ($existingByData1) {
                    Log::warning('AuroPay Callback: AuroPay transaction ID already processed in another payment request', [
                        'txnId' => $txnId,
                        'auropay_txn_id' => $transactionId,
                        'existing_payment_request_id' => $existingByData1->id,
                        'existing_transaction_id' => $existingByData1->transaction_id,
                        'current_payment_request_id' => $pr->id
                    ]);
                    
                    // Update current payment request to match the existing one to prevent duplicates
                    if ($existingByData1->status == 1 && $pr->status == 0) {
                        $pr->status = 1;
                        $pr->data1 = $transactionId;
                        $pr->data4 = $existingByData1->data4;
                        $pr->save();
                        
                        return $this->redirectToSuccessPage($pr, [
                            'txn' => $txnId,
                            'amount' => $pr->amount,
                            'utr' => $pr->data4 ?: ($pr->data1 ?? 'processed'),
                            'status' => 'success',
                        ]);
                    }
                }
                
                $statusData = $this->checkTransactionStatus($transactionId);
                
                if ($statusData && isset($statusData['transactionStatus'])) {
                    $status = $statusData['transactionStatus'];
                    
                    // AuroPay status: 2 = Authorized, 16 = Success
                    if ($status == 2 || $status == 16) {
                        // SUCCESS
                        $this->enrichPaymentRequestPayload($pr, $data, $statusData);

                        // Store original status to check if callback should be triggered
                        $originalStatus = $pr->status;
                        
                        $pr->status = 1;
                        $pr->data1 = $transactionId; // AuroPay transaction ID
                        $pr->data2 = Arr::get($statusData, 'processorName', $pr->data2);

                        $utr = Arr::get($statusData, 'traceNumber') ?: Arr::get($statusData, 'processorRefId');
                        if (!empty($utr)) {
                            $pr->data4 = $utr;
                        }

                        // Ensure callbackurl is set (fallback to user's callback if not set)
                        if (empty($pr->callbackurl)) {
                            $user = user::where('userid', $pr->userid)->first();
                            if ($user && !empty($user->callback)) {
                                $pr->callbackurl = $user->callback;
                            }
                        }

                        // Save the payment request - this will trigger the model's booted() method
                        // which will automatically call MerchantCallbackService::dispatchSuccess()
                        if ($pr->isDirty()) {
                            $pr->save();
                            
                            // Log the status change for debugging
                            Log::info('AuroPay Payment Status Updated', [
                                'txnid' => $txnId,
                                'userid' => $pr->userid,
                                'original_status' => $originalStatus,
                                'new_status' => $pr->status,
                                'callbackurl' => $pr->callbackurl,
                                'data1' => $pr->data1,
                                'data4' => $pr->data4,
                            ]);
                        }
                        
                        // Only add wallet and transaction if status was actually changed from pending (0) to success (1)
                        // This prevents duplicate wallet credits if callback is called multiple times
                        if ($originalStatus == 0) {
                            // Calculate final amount after tax
                            $finalAmount = $pr->amount - $pr->tax;
                            
                            // Add to payin wallet
                            addwallet($pr->userid, $finalAmount, '+', 'payin');
                            
                            // Add transaction record
                            addtransaction($pr->userid, 'payin', 'credit', $finalAmount, 'AuroPay', 1, $transactionId);
                            
                            Log::info('AuroPay Payment Success - Wallet Credited', [
                                'txnid' => $txnId,
                                'userid' => $pr->userid,
                                'amount' => $finalAmount,
                                'transaction_id' => $transactionId,
                            ]);
                        } else {
                            Log::info('AuroPay Payment Success - Already Processed (Skipping Wallet Credit)', [
                                'txnid' => $txnId,
                                'userid' => $pr->userid,
                                'original_status' => $originalStatus,
                            ]);
                        }
                        
                        return $this->redirectToSuccessPage($pr, [
                            'txn' => $txnId,
                            'amount' => $pr->amount,
                            'utr' => $pr->data4 ?: ($pr->data1 ?? 'processed'),
                            'status' => 'success',
                        ]);
                    } else {
                        // FAILED
                        $this->enrichPaymentRequestPayload($pr, $data, $statusData);

                        $pr->status = 2;
                        $pr->data1 = $transactionId;
                        $pr->data2 = 'Status: ' . $status;

                        if ($pr->isDirty('callback_payload') || $pr->isDirty('data1') || $pr->isDirty('data2') || $pr->isDirty('status')) {
                            $pr->save();
                        }
                        
                        return $this->redirectToFailurePage($pr, [
                            'reason' => 'Payment failed'
                        ]);
                    }
                }
            }
            
            if ($pr && $pr->isDirty('callback_payload')) {
                $pr->save();
            }

            return $this->redirectToFailurePage($pr, [
                'reason' => 'Unable to verify payment status'
            ]);
            
        } catch (\Exception $e) {
            Log::error('AuroPay Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);
            
            $txnid = $request->input('ReferenceNo', 'unknown');
            $amount = $request->input('amount', '0');
            
            return $this->redirectToFailurePage($pr ?? null, [
                'txn' => $txnid,
                'amount' => $amount,
                'reason' => 'An error occurred while processing the payment'
            ]);
        }
    }
    
    /**
     * Check transaction status by transaction ID
     */
    public function checkTransactionStatus($transactionId)
    {
        try {
            $credentials = $this->getCredentials();
            $baseUrl = $this->getBaseUrl();
            
            $response = Http::withHeaders([
                'x-version' => '1.0',
                'x-access-key' => $credentials['access_key'],
                'x-secret-key' => $credentials['secret_key'],
            ])
            ->withOptions([
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Force IPv4
                ]
            ])
            ->timeout(30)->get($baseUrl . '/api/payments/' . $transactionId);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('AuroPay Status Check Error: ' . $e->getMessage());
            return null;
        }
    }

    private function enrichPaymentRequestPayload(Payment_request $paymentRequest, array $callbackData, ?array $statusData = null): void
    {
        $existing = $paymentRequest->callback_payload;

        if (is_string($existing)) {
            $decoded = json_decode($existing, true);
            $existing = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        } elseif (!is_array($existing)) {
            $existing = [];
        }

        $merged = array_merge($existing, $callbackData);

        if ($statusData) {
            $merged['transactionStatus'] = $statusData['transactionStatus'] ?? ($merged['transactionStatus'] ?? null);
            $merged['traceNumber'] = $statusData['traceNumber'] ?? ($merged['traceNumber'] ?? null);
            $merged['processorRefId'] = $statusData['processorRefId'] ?? ($merged['processorRefId'] ?? null);
            $merged['transactionId'] = $statusData['transactionId'] ?? ($merged['transactionId'] ?? null);
            $merged['linkReferenceNumber'] = $statusData['linkReferenceNumber'] ?? ($merged['linkReferenceNumber'] ?? null);
            $merged['invoiceNo'] = $statusData['invoiceNo'] ?? ($merged['invoiceNo'] ?? null);
            $merged['processor_name'] = $statusData['processorName'] ?? ($merged['processor_name'] ?? null);
            $merged['auropay_status'] = $statusData;

            $tenderInfo = $statusData['tenderInfo'] ?? [];
            if (!empty($tenderInfo)) {
                if (!empty($tenderInfo['upiId'])) {
                    $merged['upi_va'] = $tenderInfo['upiId'];
                    if (empty($merged['mode'])) {
                        $merged['mode'] = 'UPI';
                    }
                }
                if (!empty($tenderInfo['netSettlementAmount'])) {
                    $merged['settlement_amount'] = $tenderInfo['netSettlementAmount'];
                }
                if (!empty($tenderInfo['merchantFee'])) {
                    $merged['gateway_fee'] = $tenderInfo['merchantFee'];
                }
            }

            $billing = $statusData['billingContact'] ?? [];
            if (!empty($billing)) {
                $name = $billing['name'] ?? [];
                $firstName = $name['firstName'] ?? '';
                $lastName = $name['lastName'] ?? '';
                $fullName = trim($firstName . ' ' . $lastName);

                if ($fullName !== '') {
                    if (empty($merged['firstname'])) {
                        $merged['firstname'] = $firstName;
                    }
                    if (empty($merged['lastname']) && $lastName !== '') {
                        $merged['lastname'] = $lastName;
                    }
                    if (empty($merged['customer_name'])) {
                        $merged['customer_name'] = $fullName;
                    }
                }

                if (!empty($billing['phone']) && empty($merged['phone'])) {
                    $merged['phone'] = $billing['phone'];
                }

                if (!empty($billing['email']) && empty($merged['email'])) {
                    $merged['email'] = $billing['email'];
                }
            }

            if (!empty($statusData['processorName']) && empty($merged['bank_name'])) {
                $merged['bank_name'] = $statusData['processorName'];
            }
        }

        $paymentRequest->callback_payload = $merged;
    }
    
    /**
     * Log API requests
     */
    private function logRequest($uniqueid, $data, $txnid, $userid)
    {
        try {
            $log = new Logs;
            $log->uniqueid = $uniqueid;
            $log->value = json_encode($data);
            $log->data1 = $txnid;
            $log->data2 = $userid;
            $log->save();
        } catch (\Exception $e) {
            Log::info($uniqueid, ['txnid' => $txnid, 'data' => $data]);
        }
    }
    
    /**
     * Redirect user to merchant-configured success page if available.
     */
    private function redirectToSuccessPage(Payment_request $paymentRequest, array $params)
    {
        $query = array_merge([
            'txn' => $paymentRequest->transaction_id,
            'amount' => $paymentRequest->amount,
        ], $params);

        $user = user::where('userid', $paymentRequest->userid)->first();
        if ($user && !empty($user->payin_success_redirect)) {
            $query['redirect'] = $user->payin_success_redirect;
        }

        return redirect('/payment-success?' . http_build_query($query));
    }

    private function redirectToFailurePage(?Payment_request $paymentRequest, array $params)
    {
        $query = $params;

        if ($paymentRequest) {
            $query = array_merge([
                'txn' => $paymentRequest->transaction_id,
                'amount' => $paymentRequest->amount,
            ], $params);

            $user = user::where('userid', $paymentRequest->userid)->first();
            if ($user && !empty($user->payin_success_redirect)) {
                $query['redirect'] = $user->payin_success_redirect;
            }
        }

        return redirect('/payment-failed?' . http_build_query($query));
    }
}


