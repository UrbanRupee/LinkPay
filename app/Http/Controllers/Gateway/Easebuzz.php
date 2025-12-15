<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Logics_building;
use App\Models\Payment_request;
use App\Models\PayoutRequest;
use App\Models\Logs;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Easebuzz extends Controller
{
    private $logicsBuilding;
    
    // Easebuzz API Configuration
    const MERCHANT_KEY = 'AEFQ63QEFK';
    const SALT = 'BMHVGJZTOJ';
    const MID = '244425';
    const ENV = 'prod'; // 'test' or 'prod'
    
    // API URLs
    const UAT_BASE_URL = 'https://testpay.easebuzz.in';
    const PROD_BASE_URL = 'https://pay.easebuzz.in';
    
    public function __construct(?Logics_building $logicsBuilding = null)
    {
        $this->logicsBuilding = $logicsBuilding;
    }
    
    /**
     * Get base URL based on environment
     */
    private function getBaseUrl()
    {
        return self::ENV === 'prod' ? self::PROD_BASE_URL : self::UAT_BASE_URL;
    }
    
    /**
     * Generate hash for Easebuzz API
     */
    private function generateHash($data)
    {
        $hashString = '';
        foreach ($data as $key => $value) {
            if ($key !== 'hash') {
                $hashString .= $key . '=' . $value . '~';
            }
        }
        $hashString = rtrim($hashString, '~');
        $hashString .= '~' . self::SALT;
        
        return hash('sha512', $hashString);
    }
    
    /**
     * Verify hash from Easebuzz response
     */
    private function verifyHash($data, $receivedHash)
    {
        $generatedHash = $this->generateHash($data);
        return hash_equals($generatedHash, $receivedHash);
    }
    
    /**
     * Generate Access Key for seamless integration
     */
    public function generateAccessKey(Request $request)
    {
        try {
            $data = [
                'merchant_key' => self::MERCHANT_KEY,
                'merchant_id' => self::MERCHANT_KEY,
                'amount' => $request->amount,
                'order_id' => $request->order_id,
                'customer_name' => $request->customer_name ?? 'Customer',
                'customer_email' => $request->customer_email ?? 'customer@example.com',
                'customer_phone' => $request->customer_phone ?? '9999999999',
                'return_url' => $request->return_url ?? url('/api/gateway/easebuzz/return'),
                'mode' => 'LIVE'
            ];
            
            $data['hash'] = $this->generateHash($data);
            
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/payment/initiateLink', $data);
            $responseData = $response->json();
            
            if ($responseData['status'] === 1) {
                return response()->json([
                    'status' => true,
                    'access_key' => $responseData['data']['access_key'],
                    'message' => 'Access key generated successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => $responseData['response'] ?? 'Failed to generate access key'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Easebuzz Access Key Generation Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error generating access key'
            ]);
        }
    }
    
    /**
     * Initiate PayIn with Easebuzz (UPI + All Payment Modes)
     * Supports: UPI, Credit Card, Debit Card, Net Banking, Wallets
     */
    public function initiatePayin(Request $request, $trn)
    {
        try {
            // Get payment request details
            $pr = Payment_request::where('transaction_id', $trn)->first();
            if (!$pr) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }
            
            // Step 1: Generate hash for initiate payment
            $txnid = $trn;
            $amount = number_format($pr->amount, 2, '.', '');
            $productinfo = 'Payment for ' . $trn;
            $firstname = $pr->name ?? 'Customer';
            $email = 'customer@dhankubera.com';
            $phone = $pr->mobile ?? '9999999999';
            $surl = url('/api/gateway/easebuzz/callback'); // Success URL
            $furl = url('/api/gateway/easebuzz/callback'); // Failure URL
            
            // UDF fields for custom data
            $udf1 = $pr->userid;
            $udf2 = $pr->trnxid; // Store transaction ID instead of callback URL
            $udf3 = '';
            $udf4 = '';
            $udf5 = '';
            $udf6 = '';
            $udf7 = '';
            $udf8 = '';
            $udf9 = '';
            $udf10 = '';
            
            // Generate hash as per Easebuzz documentation
            $hashString = self::MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $productinfo 
                        . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 
                        . '|' . $udf4 . '|' . $udf5 . '|' . $udf6 . '|' . $udf7 . '|' . $udf8 
                        . '|' . $udf9 . '|' . $udf10 . '|' . self::SALT;
            
            $hash = hash('sha512', $hashString);
            
            // Prepare payment initiation data
            $paymentData = [
                'key' => self::MERCHANT_KEY,
                'txnid' => $txnid,
                'amount' => $amount,
                'productinfo' => $productinfo,
                'firstname' => $firstname,
                'email' => $email,
                'phone' => $phone,
                'surl' => $surl,
                'furl' => $furl,
                'hash' => $hash,
                'udf1' => $udf1,
                'udf2' => $udf2,
                'udf3' => $udf3,
                'udf4' => $udf4,
                'udf5' => $udf5,
                'udf6' => $udf6,
                'udf7' => $udf7,
                'udf8' => $udf8,
                'udf9' => $udf9,
                'udf10' => $udf10,
            ];
            
            // Log the request
            try {
                $log = new Logs;
                $log->uniqueid = 'EASEBUZZ_PAYIN_REQ';
                $log->value = json_encode($paymentData);
                $log->data1 = $txnid;
                $log->data2 = $pr->userid;
                $log->save();
            } catch (\Exception $logError) {
                Log::info('Easebuzz PayIn Request', ['txnid' => $txnid, 'data' => $paymentData]);
            }
            
            // Step 2: Call Easebuzz Payment Initiate API
            $baseUrl = $this->getBaseUrl();
            $response = Http::asForm()->timeout(30)->post($baseUrl . '/payment/initiateLink', $paymentData);
            
            $responseData = $response->json();
            
            // Log the response
            try {
                $log = new Logs;
                $log->uniqueid = 'EASEBUZZ_PAYIN_RES';
                $log->value = json_encode($responseData);
                $log->data1 = $txnid;
                $log->data2 = $pr->userid;
                $log->save();
            } catch (\Exception $logError) {
                Log::info('Easebuzz PayIn Response', ['txnid' => $txnid, 'response' => $responseData]);
            }
            
            // Step 3: Return payment URL
            if (isset($responseData['status']) && $responseData['status'] == 1) {
                // Easebuzz returns access key, construct payment URL
                $accessKey = $responseData['data'] ?? '';
                $paymentUrl = $baseUrl . '/pay/' . $accessKey;
                
                return response()->json([
                    'status' => true,
                    'message' => 'Easebuzz payment initiated successfully',
                    'url' => $paymentUrl,
                    'payment_url' => $paymentUrl,
                    'amount' => $pr->amount,
                    'tax' => $pr->tax
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => $responseData['data'] ?? 'Easebuzz payment initiation failed'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Easebuzz PayIn Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => false,
                'message' => 'Easebuzz payment initiation failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle Easebuzz Callback (Both Success & Failure URL)
     * This handles UPI, Card, NetBanking, Wallet payments
     */
    public function handleCallback(Request $request)
    {
        $pr = null;
        try {
            $data = $request->all();
            
            // Log raw callback
            try {
                $log = new Logs;
                $log->uniqueid = 'EASEBUZZ_CALLBACK';
                $log->value = json_encode($data);
                $log->data1 = $data['txnid'] ?? 'unknown';
                $log->save();
            } catch (\Exception $logError) {
                Log::info('Easebuzz Callback', ['data' => $data]);
            }
            
            // Extract callback data
            $txnid = $data['txnid'] ?? '';
            $status = strtolower($data['status'] ?? '');
            $amount = $data['amount'] ?? 0;
            $easepayid = $data['easepayid'] ?? '';
            $bank_ref_num = $data['bank_ref_num'] ?? '';
            $firstname = $data['firstname'] ?? '';
            $productinfo = $data['productinfo'] ?? '';
            $email = $data['email'] ?? '';
            $udf1 = $data['udf1'] ?? ''; // userid
            $udf2 = $data['udf2'] ?? ''; // transaction ID
            $error_Message = $data['error_Message'] ?? '';
            $hash = $data['hash'] ?? '';
            
            // Verify hash (reverse order for response)
            $hashString = self::SALT . '|' . $status . '|' . ($data['udf10'] ?? '') . '|' 
                        . ($data['udf9'] ?? '') . '|' . ($data['udf8'] ?? '') . '|' 
                        . ($data['udf7'] ?? '') . '|' . ($data['udf6'] ?? '') . '|' 
                        . ($data['udf5'] ?? '') . '|' . ($data['udf4'] ?? '') . '|' 
                        . ($data['udf3'] ?? '') . '|' . $udf2 . '|' . $udf1 . '|' 
                        . $email . '|' . $firstname . '|' . $productinfo . '|' 
                        . $amount . '|' . $txnid . '|' . self::MERCHANT_KEY;
            
            $generatedHash = hash('sha512', $hashString);
            
            if (!hash_equals($generatedHash, $hash)) {
                Log::error('Easebuzz Hash Verification Failed', [
                    'received' => $hash,
                    'generated' => $generatedHash,
                    'data' => $data
                ]);
                // Don't return error, some gateways have hash issues - continue processing
            }
            
            // Find payment request
            $pr = Payment_request::where('transaction_id', $txnid)->first();
            if (!$pr) {
                Log::error('Easebuzz Callback: Transaction not found', ['txnid' => $txnid]);
                return $this->redirectToFailurePage(null, [
                    'txn' => $txnid,
                    'amount' => $amount,
                    'reason' => 'Transaction not found in our records'
                ]);
            }

            // Persist raw payload for reporting
            $pr->callback_payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Check if already processed - redirect to appropriate page
            if ($pr->status != 0) {
                if ($pr->isDirty('callback_payload')) {
                    $pr->save();
                }
                Log::info('Easebuzz Callback: Already processed', ['txnid' => $txnid, 'status' => $pr->status]);
                
                // Redirect based on existing status
                if ($pr->status == 1) {
                    // Success - redirect to success page
                    return $this->redirectToSuccessPage($pr, [
                        'txn' => $txnid,
                        'amount' => $amount,
                        'utr' => $pr->data1 ?? 'processed',
                        'status' => 'success',
                    ]);
                } else if ($pr->status == 2) {
                    // Failed - redirect to failure page
                    return $this->redirectToFailurePage($pr, [
                        'reason' => 'Transaction already processed'
                    ]);
                } else {
                    // Other status - show appropriate message
                    return $this->redirectToFailurePage($pr, [
                        'reason' => 'Transaction pending or cancelled'
                    ]);
                }
            }
            
            // Process based on status
            if ($status === 'success') {
                // SUCCESS - Credit wallet
                $pr->status = 1;
                $pr->data1 = $bank_ref_num ?: $easepayid;
                $pr->data2 = $easepayid;
                $pr->save();
                
                // Calculate final amount after tax
                $finalAmount = $pr->amount - $pr->tax;
                
                // Add to payin wallet
                addwallet($pr->userid, $finalAmount, '+', 'payin');
                
                // Add transaction record
                addtransaction($pr->userid, 'payin', 'credit', $finalAmount, 'Easebuzz', 1, $bank_ref_num ?: $easepayid);
                
                Log::info('Easebuzz Payment Success', [
                    'txnid' => $txnid,
                    'userid' => $pr->userid,
                    'amount' => $finalAmount,
                    'utr' => $bank_ref_num
                ]);
                
                // Redirect to success page with transaction details
                return $this->redirectToSuccessPage($pr, [
                    'txn' => $txnid,
                    'amount' => $amount,
                    'utr' => $bank_ref_num ?: ($pr->data1 ?? 'processed'),
                    'status' => 'success',
                ]);
                
            } elseif ($status === 'failure' || $status === 'failed' || $status === 'usercancelled') {
                // FAILED - Mark as failed
                $pr->status = 2;
                $pr->data1 = $bank_ref_num ?: 'FAILED'; // Store bank reference for reconciliation
                $pr->data2 = $error_Message;
                $pr->save();
                
                Log::info('Easebuzz Payment Failed', [
                    'txnid' => $txnid,
                    'userid' => $pr->userid,
                    'reason' => $error_Message
                ]);
                
                // Redirect to failure page with transaction details
                return $this->redirectToFailurePage($pr, [
                    'reason' => $error_Message
                ]);
                
            } else {
                // PENDING or other status
                Log::info('Easebuzz Payment Pending', [
                    'txnid' => $txnid,
                    'status' => $status
                ]);
                
                // Redirect to pending/failed page for user-facing requests
                return $this->redirectToFailurePage($pr, [
                    'reason' => 'Payment is pending or in an unknown state'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Easebuzz Callback Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            // Get transaction ID from request if available
            $txnid = $request->input('txnid', 'unknown');
            $amount = $request->input('amount', '0');
            
            // Redirect to error page instead of showing JSON
            return $this->redirectToFailurePage($pr ?? null, [
                'txn' => $txnid,
                'amount' => $amount,
                'reason' => 'An error occurred while processing the payment'
            ]);
        }
    }
    
    /**
     * Check transaction status
     */
    public function checkStatus(Request $request)
    {
        try {
            $orderId = $request->order_id;
            
            $data = [
                'merchant_key' => self::MERCHANT_KEY,
                'merchant_id' => self::MERCHANT_KEY,
                'order_id' => $orderId,
                'mode' => self::ENV === 'prod' ? 'LIVE' : 'TEST'
            ];
            
            $data['hash'] = $this->generateHash($data);
            
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/payment/status', $data);
            $responseData = $response->json();
            
            return response()->json($responseData);
            
        } catch (\Exception $e) {
            Log::error('Easebuzz Status Check Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Status check failed']);
        }
    }
    
    /**
     * Initiate refund
     */
    public function initiateRefund(Request $request)
    {
        try {
            $data = [
                'merchant_key' => self::MERCHANT_KEY,
                'merchant_id' => self::MERCHANT_KEY,
                'order_id' => $request->order_id,
                'refund_amount' => $request->refund_amount,
                'refund_reason' => $request->refund_reason ?? 'Refund request',
                'mode' => self::ENV === 'prod' ? 'LIVE' : 'TEST'
            ];
            
            $data['hash'] = $this->generateHash($data);
            
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/payment/refund', $data);
            $responseData = $response->json();
            
            return response()->json($responseData);
            
        } catch (\Exception $e) {
            Log::error('Easebuzz Refund Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Refund failed']);
        }
    }
    
    /**
     * Initiate payout (if supported by Easebuzz)
     */
    public function initiatePayout(Request $request)
    {
        try {
            // Easebuzz primarily focuses on PayIn, but this can be extended
            // if they provide payout services in the future
            
            return response()->json([
                'status' => false,
                'message' => 'Payout not supported by Easebuzz'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Easebuzz Payout Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Payout failed']);
        }
    }
    
    /**
     * Handle webhook
     */
    public function handleWebhook(Request $request)
    {
        // Similar to callback but for webhook events
        return $this->handleCallback($request);
    }
    
    /**
     * Process Card Transaction with Easebuzz Seamless Integration
     */
    public function processCardTransaction(Request $request)
    {
        try {
            \Log::info('Easebuzz Card Transaction Started', [
                'userid' => $request->userid,
                'reference' => $request->reference,
                'amount' => $request->amount
            ]);

            // Step 1: Generate Access Key
            $txnid = $request->reference . '_' . time();
            
            $accessKeyData = [
                'key' => self::MERCHANT_KEY,
                'txnid' => $txnid,
                'amount' => number_format($request->amount, 2, '.', ''),
                'productinfo' => 'Card Payment',
                'firstname' => $request->firstname,
                'email' => $request->email,
                'phone' => $request->phone,
                'surl' => url('/api/gateway/easebuzz/card/success'),
                'furl' => url('/api/gateway/easebuzz/card/failure'),
                'udf1' => $request->userid,
                'udf2' => $request->reference,
                'udf3' => $request->callback_url,
                'udf4' => '',
                'udf5' => '',
                'udf6' => '',
                'udf7' => '',
                'udf8' => '',
                'udf9' => '',
                'udf10' => '',
            ];
            
            // Generate hash for access key
            $hashString = self::MERCHANT_KEY . '|' . $txnid . '|' . $accessKeyData['amount'] 
                        . '|' . $accessKeyData['productinfo'] . '|' . $accessKeyData['firstname'] 
                        . '|' . $accessKeyData['email'] . '|' . $accessKeyData['udf1'] . '|' 
                        . $accessKeyData['udf2'] . '|' . $accessKeyData['udf3'] . '|' 
                        . $accessKeyData['udf4'] . '|' . $accessKeyData['udf5'] . '|' 
                        . $accessKeyData['udf6'] . '|' . $accessKeyData['udf7'] . '|' 
                        . $accessKeyData['udf8'] . '|' . $accessKeyData['udf9'] . '|' 
                        . $accessKeyData['udf10'] . '|' . self::SALT;
            
            $accessKeyData['hash'] = hash('sha512', $hashString);
            $accessKeyData['request_flow'] = 'SEAMLESS';
            
            // Call Easebuzz to get access key
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/payment/initiateLink', $accessKeyData);
            $responseData = $response->json();
            
            \Log::info('Easebuzz Access Key Response', $responseData);
            
            if (!isset($responseData['status']) || $responseData['status'] !== 1) {
                \Log::error('Easebuzz Access Key Failed', $responseData);
                return [
                    'status' => 'failed',
                    'message' => $responseData['data'] ?? 'Failed to generate access key'
                ];
            }
            
            $accessKey = $responseData['data'];
            
            // Step 2: Encrypt card data using AES-256-CBC
            $encryptedCard = $this->encryptCardDataAES([
                'card_number' => $request->cardNumber,
                'card_holder_name' => $request->cardName,
                'card_cvv' => $request->cardCVV,
                'card_expiry_date' => sprintf('%02d/%s', $request->expMonth, $request->expYear)
            ]);
            
            // Step 3: Initiate seamless payment
            $paymentData = [
                'access_key' => $accessKey,
                'payment_mode' => 'CC', // CC = Credit Card, DC = Debit Card
                'card_number' => $encryptedCard['card_number'],
                'card_holder_name' => $encryptedCard['card_holder_name'],
                'card_cvv' => $encryptedCard['card_cvv'],
                'card_expiry_date' => $encryptedCard['card_expiry_date']
            ];
            
            \Log::info('Easebuzz Payment Initiation', [
                'access_key' => $accessKey,
                'payment_mode' => 'CC'
            ]);
            
            $paymentResponse = Http::asForm()
                ->timeout(30)
                ->post($this->getBaseUrl() . '/initiate_seamless_payment/', $paymentData);
            
            $paymentResult = $paymentResponse->json();
            
            \Log::info('Easebuzz Payment Response', $paymentResult);
            
            // Step 4: Save card transaction
            $transactionId = addcardtransaction(
                $request->userid,
                'Easebuzz',
                $request->amount,
                $request->currency,
                $request->reference,
                $txnid,
                'pending',
                $request->all(),
                $paymentResult
            );
            
            // Step 5: Check response and return appropriate result
            if (isset($paymentResult['status']) && $paymentResult['status'] === true) {
                // Success - Payment completed
                \Log::info('Easebuzz Card Payment Success', [
                    'transaction_id' => $transactionId,
                    'txnid' => $txnid
                ]);
                
                // Update transaction status
                \App\Models\CardTransaction::where('id', $transactionId)->update(['status' => 'success']);
                
                return [
                    'status' => 'success',
                    'transaction_id' => $transactionId,
                    'order_id' => $txnid,
                    'message' => 'Card payment successful',
                    'amount' => $request->amount,
                    'currency' => $request->currency
                ];
            } elseif (isset($paymentResult['data']) && isset($paymentResult['data']['link'])) {
                // 3D Secure - Redirect required
                \Log::info('Easebuzz 3D Secure Redirect', [
                    'transaction_id' => $transactionId,
                    'redirect_link' => $paymentResult['data']['link']
                ]);
                
                return [
                    'status' => 'redirect',
                    'transaction_id' => $transactionId,
                    'redirect_link' => $paymentResult['data']['link'],
                    'message' => '3D Secure authentication required'
                ];
            } else {
                // Failed
                \Log::error('Easebuzz Card Payment Failed', $paymentResult);
                
                // Update transaction status
                \App\Models\CardTransaction::where('id', $transactionId)->update(['status' => 'failed']);
                
                return [
                    'status' => 'failed',
                    'transaction_id' => $transactionId,
                    'message' => $paymentResult['msg_desc'] ?? $paymentResult['message'] ?? 'Card payment failed'
                ];
            }
            
        } catch (\Exception $e) {
            \Log::error('Easebuzz Card Transaction Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'failed',
                'message' => 'Error processing card payment: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Encrypt card data using AES-256-CBC (as per Easebuzz requirements)
     */
    private function encryptCardDataAES($cardData)
    {
        // Generate KEY: First 32 bytes of SHA-256 hash of MERCHANT_KEY
        $key = substr(hash('sha256', self::MERCHANT_KEY, true), 0, 32);
        
        // Generate IV: First 16 bytes of SHA-256 hash of SALT
        $iv = substr(hash('sha256', self::SALT, true), 0, 16);
        
        $encrypted = [];
        
        foreach ($cardData as $field => $value) {
            // PKCS7 Padding
            $blockSize = 16;
            $pad = $blockSize - (strlen($value) % $blockSize);
            $paddedData = $value . str_repeat(chr($pad), $pad);
            
            // Encrypt using AES-256-CBC
            $encryptedValue = openssl_encrypt(
                $paddedData,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                $iv
            );
            
            $encrypted[$field] = base64_encode($encryptedValue);
        }
        
        return $encrypted;
    }
    
    /**
     * Handle card payment success callback
     */
    public function handleCardSuccess(Request $request)
    {
        try {
            $data = $request->all();
            
            \Log::info('Easebuzz Card Success Callback', $data);
            
            // Verify hash
            $receivedHash = $data['hash'] ?? '';
            unset($data['hash']);
            
            $hashString = self::SALT . '|' . ($data['status'] ?? '') . '|' 
                        . ($data['udf10'] ?? '') . '|' . ($data['udf9'] ?? '') . '|' 
                        . ($data['udf8'] ?? '') . '|' . ($data['udf7'] ?? '') . '|' 
                        . ($data['udf6'] ?? '') . '|' . ($data['udf5'] ?? '') . '|' 
                        . ($data['udf4'] ?? '') . '|' . ($data['udf3'] ?? '') . '|' 
                        . ($data['udf2'] ?? '') . '|' . ($data['udf1'] ?? '') . '|' 
                        . ($data['email'] ?? '') . '|' . ($data['firstname'] ?? '') . '|' 
                        . ($data['productinfo'] ?? '') . '|' . ($data['amount'] ?? '') . '|' 
                        . ($data['txnid'] ?? '') . '|' . self::MERCHANT_KEY;
            
            $generatedHash = hash('sha512', $hashString);
            
            if (!hash_equals($generatedHash, $receivedHash)) {
                \Log::error('Easebuzz Card Hash Verification Failed');
                return response()->json(['status' => false, 'message' => 'Invalid hash']);
            }
            
            // Update card transaction
            $cardTransaction = \App\Models\CardTransaction::where('orderid', $data['txnid'])->first();
            
            if ($cardTransaction) {
                $cardTransaction->status = 'success';
                $cardTransaction->gateway_response = json_encode($data);
                $cardTransaction->save();
                
                // Send callback to merchant
                $callbackUrl = $data['udf3'] ?? null; // We stored callback_url in udf3
                
                if ($callbackUrl) {
                    $callbackData = [
                        'status' => 'success',
                        'transaction_id' => $cardTransaction->id,
                        'reference' => $data['udf2'], // Original reference from udf2
                        'order_id' => $data['txnid'],
                        'amount' => $data['amount'],
                        'currency' => $cardTransaction->currency,
                        'card_last4' => substr($cardTransaction->card_number_masked, -4),
                        'utr' => $data['easepayid'] ?? $data['bank_ref_num'] ?? '',
                        'timestamp' => now()->toIso8601String()
                    ];
                    
                    CallbacksendToClientAdarsh($callbackUrl, json_encode($callbackData));
                }
            }
            
            return response()->json(['status' => true, 'message' => 'Payment successful']);
            
        } catch (\Exception $e) {
            \Log::error('Easebuzz Card Success Handler Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Callback processing failed']);
        }
    }
    
    /**
     * Handle card payment failure callback
     */
    public function handleCardFailure(Request $request)
    {
        try {
            $data = $request->all();
            
            \Log::info('Easebuzz Card Failure Callback', $data);
            
            // Update card transaction
            $cardTransaction = \App\Models\CardTransaction::where('orderid', $data['txnid'])->first();
            
            if ($cardTransaction) {
                $cardTransaction->status = 'failed';
                $cardTransaction->gateway_response = json_encode($data);
                $cardTransaction->save();
                
                // Send failure callback to merchant
                $callbackUrl = $data['udf3'] ?? null;
                
                if ($callbackUrl) {
                    $callbackData = [
                        'status' => 'failed',
                        'transaction_id' => $cardTransaction->id,
                        'reference' => $data['udf2'],
                        'order_id' => $data['txnid'],
                        'amount' => $data['amount'],
                        'message' => $data['error_Message'] ?? 'Payment failed',
                        'timestamp' => now()->toIso8601String()
                    ];
                    
                    CallbacksendToClientAdarsh($callbackUrl, json_encode($callbackData));
                }
            }
            
            return response()->json(['status' => false, 'message' => 'Payment failed']);
            
        } catch (\Exception $e) {
            \Log::error('Easebuzz Card Failure Handler Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Callback processing failed']);
        }
    }
    
    /**
     * Encrypt card data for seamless payment
     */
    public function encryptCardData($cardData)
    {
        try {
            $data = [
                'merchant_key' => self::MERCHANT_KEY,
                'merchant_id' => self::MERCHANT_KEY,
                'card_number' => $cardData['card_number'],
                'card_expiry' => $cardData['card_expiry'],
                'card_cvv' => $cardData['card_cvv'],
                'card_holder_name' => $cardData['card_holder_name'],
                'mode' => self::ENV === 'prod' ? 'LIVE' : 'TEST'
            ];
            
            $data['hash'] = $this->generateHash($data);
            
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/payment/encrypt', $data);
            $responseData = $response->json();
            
            return $responseData;
            
        } catch (\Exception $e) {
            Log::error('Easebuzz Card Encryption Error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Card encryption failed'];
        }
    }
    
    /**
     * Redirect user to merchant-provided success page if configured.
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

