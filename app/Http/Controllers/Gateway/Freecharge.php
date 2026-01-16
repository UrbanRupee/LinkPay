<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Logics_building;
use App\Models\Payment_request;
use App\Models\Logs;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Freecharge (Axis Payment Gateway) Integration
 * Based on official API documentation
 */
class Freecharge extends Controller
{
    private $logicsBuilding;
    
    // Configuration
    const MERCHANT_ID = 'MER82620b8df7';
    const SECRET_KEY_LIVE = 'aFor7b+PvWJdFU09wGOODg6dj6h5A7vHsZF8bsVHzXY=';
    const SECRET_KEY_TEST = '9vLNGyS0JcEiAJjhc5YBciTX4VDH6OAab4+WB1U8Hww=';
    const ENV = 'production'; // 'production' or 'sandbox'
    
    const BASE_URL_PROD = 'https://secure-axispg.freecharge.in';
    const BASE_URL_SANDBOX = 'https://sandbox-axispg.freecharge.in';
    
    public function __construct(?Logics_building $logicsBuilding = null)
    {
        $this->logicsBuilding = $logicsBuilding;
    }
    
    /**
     * Get base URL based on environment
     */
    private function getBaseUrl()
    {
        return self::ENV === 'production' ? self::BASE_URL_PROD : self::BASE_URL_SANDBOX;
    }
    
    /**
     * Get secret key based on environment
     */
    private function getSecretKey()
    {
        return self::ENV === 'production' ? self::SECRET_KEY_LIVE : self::SECRET_KEY_TEST;
    }
    
    /**
     * Generate signature for checkout request
     * According to documentation:
     * 1. Concatenate all non-null field values in ascending order of field names
     * 2. Append merchant key
     * 3. SHA-256 hash
     * 4. Hex encode
     * 
     * For checkout: merchantId, callbackUrl, merchantTxnId, merchantTxnAmount, currency, timestamp
     */
    private function generateCheckoutSignature($data)
    {
        // Fields for signature (in alphabetical order as per documentation)
        $signatureFields = [
            'callbackUrl',
            'currency',
            'merchantId',
            'merchantTxnAmount',
            'merchantTxnId',
            'timestamp'
        ];
        
        $concatenatedString = '';
        
        foreach ($signatureFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                $value = $data[$field];
                
                // Format merchantTxnAmount to 1 decimal place
                if ($field === 'merchantTxnAmount') {
                    $value = number_format((float)$value, 1, '.', '');
                }
                
                $concatenatedString .= (string)$value;
            }
        }
        
        // Append secret key
        $concatenatedString .= $this->getSecretKey();
        
        // SHA-256 hash and hex encode
        $signature = hash('sha256', $concatenatedString);
        
        return $signature;
    }
    
    /**
     * Generate signature for callback verification
     * According to documentation: specific field order (not alphabetical)
     * Fields: amount, currency, handlingFee, merchantTxnId, mode, statusCode, 
     *        statusMessage, subMerchantPayInfo, subMode, taxAmount, txnReferenceId
     */
    private function generateCallbackSignature($data)
    {
        $orderedFields = [
            'amount',
            'currency',
            'handlingFee',
            'merchantTxnId',
            'mode',
            'statusCode',
            'statusMessage',
            'subMerchantPayInfo',
            'subMode',
            'taxAmount',
            'txnReferenceId'
        ];
        
        $decimalFields = ['amount', 'handlingFee', 'taxAmount'];
        $concatenatedString = '';
        
        foreach ($orderedFields as $field) {
            $value = $data[$field] ?? null;
            
            // Special handling for subMerchantPayInfo
            if ($field === 'subMerchantPayInfo') {
                $concatenatedString .= ($value !== null && $value !== '') ? (string)$value : '';
                continue;
            }
            
            // Format decimal fields to 1 decimal place
            if (in_array($field, $decimalFields)) {
                if ($value !== null && $value !== '' && $value !== 'null') {
                    $concatenatedString .= number_format((float)$value, 1, '.', '');
                } else {
                    $concatenatedString .= '0.0';
                }
                continue;
            }
            
            // Other fields
            if ($value !== null && $value !== '' && $value !== 'null') {
                $concatenatedString .= (string)$value;
            }
        }
        
        // Append secret key
        $concatenatedString .= $this->getSecretKey();
        
        // SHA-256 hash and hex encode
        $signature = hash('sha256', $concatenatedString);
        
        return $signature;
    }
    
    /**
     * Get latest request body sent to Freecharge
     * For debugging purposes
     */
    public function getLatestRequestBody(Request $request)
    {
        try {
            $txnId = $request->input('txn_id');
            
            $query = Logs::where('uniqueid', 'FREECHARGE_REQUEST_BODY')
                ->orderBy('id', 'desc');
            
            if ($txnId) {
                $query->where('data1', $txnId);
            }
            
            $log = $query->first();
            
            if ($log) {
                $data = json_decode($log->value, true);
                return response()->json([
                    'status' => true,
                    'data' => $data,
                    'txn_id' => $log->data1,
                    'timestamp' => $log->data2
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No request body found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all Freecharge logs from database
     * For debugging purposes
     */
    public function getLogs(Request $request)
    {
        try {
            $limit = $request->input('limit', 50);
            $txnId = $request->input('txn_id');
            $type = $request->input('type', 'all'); // 'all', 'request', 'callback', 'payload'
            
            $query = Logs::where('uniqueid', 'LIKE', 'FREECHARGE%')
                ->orderBy('id', 'desc')
                ->limit($limit);
            
            if ($txnId) {
                $query->where('data1', $txnId);
            }
            
            if ($type === 'request') {
                $query->where('uniqueid', 'FREECHARGE_REQUEST_BODY');
            } elseif ($type === 'callback') {
                $query->where('uniqueid', 'FREECHARGE_CALLBACK');
            } elseif ($type === 'payload') {
                $query->where('uniqueid', 'LIKE', 'FREECHARGE_PAYLOAD%');
            }
            
            $logs = $query->get();
            
            $formattedLogs = $logs->map(function($log) {
                $data = json_decode($log->value, true);
                return [
                    'id' => $log->id,
                    'uniqueid' => $log->uniqueid,
                    'txn_id' => $log->data1,
                    'timestamp' => $log->data2,
                    'created_at' => $log->created_at,
                    'data' => $data
                ];
            });
            
            return response()->json([
                'status' => true,
                'count' => $formattedLogs->count(),
                'logs' => $formattedLogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Freecharge logs from Laravel log file
     * For debugging purposes
     */
    public function getLaravelLogs(Request $request)
    {
        try {
            $lines = $request->input('lines', 200);
            $search = $request->input('search', 'Freecharge');
            
            $logPath = storage_path('logs/laravel.log');
            
            if (!file_exists($logPath)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Log file not found'
                ], 404);
            }
            
            // Read last N lines and filter for Freecharge
            $command = "tail -n {$lines} {$logPath} | grep -i '{$search}'";
            $logContent = shell_exec($command);
            
            if (empty($logContent)) {
                return response()->json([
                    'status' => true,
                    'message' => 'No Freecharge logs found in last ' . $lines . ' lines',
                    'logs' => []
                ]);
            }
            
            // Parse log entries
            $logLines = explode("\n", trim($logContent));
            $parsedLogs = [];
            
            foreach ($logLines as $line) {
                if (empty(trim($line))) continue;
                
                // Try to extract JSON from log line
                if (preg_match('/\{.*\}/', $line, $matches)) {
                    $json = json_decode($matches[0], true);
                    if ($json) {
                        $parsedLogs[] = [
                            'raw' => $line,
                            'parsed' => $json
                        ];
                    } else {
                        $parsedLogs[] = [
                            'raw' => $line,
                            'parsed' => null
                        ];
                    }
                } else {
                    $parsedLogs[] = [
                        'raw' => $line,
                        'parsed' => null
                    ];
                }
            }
            
            return response()->json([
                'status' => true,
                'count' => count($parsedLogs),
                'logs' => $parsedLogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate test transaction
     */
    public function generateTestTransaction(Request $request)
    {
        try {
            $userid = $request->input('userid', 'GWSDK1115');
            $amount = $request->input('amount', 10);
            
            // Generate unique transaction ID
            $trn = 'FC' . time() . rand(10000, 99999);
            
            // Create payment request
            $pr = new Payment_request();
            $pr->transaction_id = $trn;
            $pr->userid = $userid;
            $pr->amount = $amount;
            $pr->data6 = 30; // Freecharge gateway ID
            $pr->data3 = 1;
            $pr->status = 0;
            $pr->mobile = $request->input('mobile', '9999999999');
            $pr->name = $request->input('name', 'Test User');
            
            if ($pr->save()) {
                return response()->json([
                    'status' => true,
                    'transaction_id' => $trn,
                    'message' => 'Transaction created successfully',
                    'userid' => $userid,
                    'amount' => $amount
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create payment request'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get payment payload for direct frontend submission (V1 - Form URL Encoded)
     * Returns payload with signature so frontend can submit directly to Freecharge
     * 
     * @deprecated Use getPaymentPayloadV3() for new integrations
     */
    public function getPaymentPayload(Request $request, $trn)
    {
        try {
            $pr = Payment_request::where('transaction_id', $trn)->first();
            if (!$pr) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }
            
            $merchantTxnId = $trn;
            $amount = (float)$pr->amount;
            $callbackUrl = $request->input('callbackUrl', url('/api/gateway/freecharge/callback'));
            $redirectUrl = $request->input('redirectUrl', url('/payment-success?txn=' . $merchantTxnId));
            
            // Generate fresh timestamp
            $timestamp = time();
            
            // Build payment payload
            $payload = [
                'merchantId' => self::MERCHANT_ID,
                'currency' => 'INR',
                'callbackUrl' => $callbackUrl,
                'redirectUrl' => $redirectUrl, // User redirect after payment
                'timestamp' => $timestamp,
                'merchantTxnAmount' => number_format($amount, 1, '.', ''),
                'merchantTxnId' => $merchantTxnId,
            ];
            
            // Generate signature
            $signature = $this->generateCheckoutSignature($payload);
            $payload['signature'] = $signature;
            
            // Build payload in EXACT order for Freecharge (important for form submission)
            // Order: merchantId, currency, callbackUrl, timestamp, merchantTxnAmount, merchantTxnId, signature
            $orderedPayload = [
                'merchantId' => $payload['merchantId'],
                'currency' => $payload['currency'],
                'callbackUrl' => $payload['callbackUrl'],
                'timestamp' => $payload['timestamp'],
                'merchantTxnAmount' => $payload['merchantTxnAmount'],
                'merchantTxnId' => $payload['merchantTxnId'],
                'signature' => $payload['signature'],
            ];
            
            $checkoutUrl = $this->getBaseUrl() . '/payment/v1/checkout';
            
            // Build URL-encoded string (exact format that will be sent)
            $urlEncoded = http_build_query($orderedPayload);
            
            // Log the exact request body that will be sent
            Log::info('Freecharge: Payload Generated for Direct Frontend Submission (V1)', [
                'txnid' => $merchantTxnId,
                'method' => 'CLIENT-TO-SERVER (Frontend will POST directly to Freecharge)',
                'checkout_url' => $checkoutUrl,
                'payload' => $orderedPayload,
                'url_encoded' => $urlEncoded,
                'timestamp' => $timestamp,
                'timestamp_age_seconds' => 0, // Generated just now
                'note' => 'Frontend must submit IMMEDIATELY - URL expires in seconds'
            ]);
            
            return response()->json([
                'status' => true,
                'checkout_url' => $checkoutUrl,
                'payload' => $orderedPayload, // Return in correct order
                'timestamp' => $timestamp,
                'url_encoded_body' => $urlEncoded, // Show exact format
                'method' => 'CLIENT-TO-SERVER',
                'instruction' => 'Frontend must POST this payload directly to checkout_url IMMEDIATELY'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Freecharge: Error generating payload', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get payment payload for V3 checkout (JSON API)
     * POST /payment/v3/checkout
     * Content-Type: application/json
     * 
     * V3 API uses JSON instead of form-urlencoded
     * Returns checkout ID and redirect URLs
     */
    public function getPaymentPayloadV3(Request $request, $trn)
    {
        try {
            $pr = Payment_request::where('transaction_id', $trn)->first();
            if (!$pr) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }
            
            $merchantTxnId = $trn;
            $amount = (float)$pr->amount;
            $callbackUrl = $request->input('callbackUrl', url('/api/gateway/freecharge/callback'));
            $redirectUrl = $request->input('redirectUrl', url('/payment-success?txn=' . $merchantTxnId));
            
            // Generate fresh timestamp
            $timestamp = time();
            
            // Build payment payload for V3 (JSON format)
            $payload = [
                'merchantId' => self::MERCHANT_ID,
                'callbackUrl' => $callbackUrl,
                'redirectUrl' => $redirectUrl, // User redirect after payment
                'merchantTxnId' => $merchantTxnId,
                'merchantTxnAmount' => (float)number_format($amount, 1, '.', ''), // V3 expects number, not string
                'currency' => 'INR',
                'timestamp' => $timestamp,
            ];
            
            // Add optional customer fields if provided
            if ($request->has('customerName')) {
                $payload['customerName'] = $request->input('customerName');
            }
            if ($request->has('customerEmailId')) {
                $payload['customerEmailId'] = $request->input('customerEmailId');
            }
            if ($request->has('customerMobileNo')) {
                $payload['customerMobileNo'] = $request->input('customerMobileNo');
            }
            if ($request->has('customerId')) {
                $payload['customerId'] = $request->input('customerId');
            }
            
            // Generate signature (same logic as V1 - alphabetical order of non-null fields)
            $signature = $this->generateCheckoutSignature($payload);
            $payload['signature'] = $signature;
            
            $checkoutUrl = $this->getBaseUrl() . '/payment/v3/checkout';
            
            // Log the request
            Log::info('Freecharge V3: Payload Generated for Checkout', [
                'txnid' => $merchantTxnId,
                'method' => 'V3 JSON API',
                'checkout_url' => $checkoutUrl,
                'payload' => $payload,
                'timestamp' => $timestamp,
                'timestamp_age_seconds' => 0,
            ]);
            
            return response()->json([
                'status' => true,
                'checkout_url' => $checkoutUrl,
                'payload' => $payload,
                'timestamp' => $timestamp,
                'method' => 'V3_JSON_API',
                'instruction' => 'Frontend should POST this JSON payload to checkout_url with Content-Type: application/json'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Freecharge V3: Error generating payload', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Initiate V3 Checkout - Server-to-Server call
     * POST /payment/v3/checkout
     * Content-Type: application/json
     * 
     * This method calls Freecharge V3 API directly and returns checkout ID
     */
    public function initiateCheckoutV3(Request $request, $trn)
    {
        try {
            $pr = Payment_request::where('transaction_id', $trn)->first();
            if (!$pr) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }
            
            $merchantTxnId = $trn;
            $amount = (float)$pr->amount;
            $callbackUrl = $request->input('callbackUrl', url('/api/gateway/freecharge/callback'));
            
            // Generate fresh timestamp
            $timestamp = time();
            
            // Build request payload for V3
            $requestPayload = [
                'merchantId' => self::MERCHANT_ID,
                'callbackUrl' => $callbackUrl,
                'merchantTxnId' => $merchantTxnId,
                'merchantTxnAmount' => (float)number_format($amount, 1, '.', ''),
                'currency' => 'INR',
                'timestamp' => $timestamp,
            ];
            
            // Add optional customer fields
            if ($request->has('customerName')) {
                $requestPayload['customerName'] = $request->input('customerName');
            }
            if ($request->has('customerEmailId')) {
                $requestPayload['customerEmailId'] = $request->input('customerEmailId');
            }
            if ($request->has('customerMobileNo')) {
                $requestPayload['customerMobileNo'] = $request->input('customerMobileNo');
            }
            if ($request->has('customerId')) {
                $requestPayload['customerId'] = $request->input('customerId');
            }
            
            // Generate signature
            $signature = $this->generateCheckoutSignature($requestPayload);
            $requestPayload['signature'] = $signature;
            
            $checkoutUrl = $this->getBaseUrl() . '/payment/v3/checkout';
            
            // Log request
            Log::info('Freecharge V3: Initiating Checkout', [
                'txnid' => $merchantTxnId,
                'url' => $checkoutUrl,
                'payload' => $requestPayload,
                'timestamp' => $timestamp,
            ]);
            
            // Make API call to Freecharge V3
            $response = Http::asJson()
                ->timeout(30)
                ->withOptions([
                    'connect_timeout' => 10,
                    'verify' => true
                ])
                ->post($checkoutUrl, $requestPayload);
            
            $statusCode = $response->status();
            $responseBody = $response->json();
            
            // Log response
            Log::info('Freecharge V3: Checkout Response', [
                'txnid' => $merchantTxnId,
                'status_code' => $statusCode,
                'response' => $responseBody,
            ]);
            
            if ($statusCode === 200 && isset($responseBody['statusCode']) && $responseBody['statusCode'] === 'SPG-0000') {
                // Success - return checkout ID and redirect URL
                $checkoutId = $responseBody['data']['id'] ?? null;
                $baseUrl = $this->getBaseUrl();
                
                // V3 checkout page URL - this is where user should be redirected
                // Note: V3 may require checkoutId as cookie, but we cannot set cookies for Freecharge domain
                // Freecharge should handle cookie setting when user accesses the checkout page
                $checkoutPageUrl = $baseUrl . '/payment/v3/checkout/' . $checkoutId;
                
                // Log success
                Log::info('Freecharge V3: Checkout created successfully', [
                    'txnid' => $merchantTxnId,
                    'checkout_id' => $checkoutId,
                    'checkout_url' => $checkoutPageUrl,
                    'note' => 'Redirect user to checkout_url immediately - checkout expires quickly'
                ]);
                
                // If request expects JSON, return JSON with redirect info
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Checkout created successfully',
                        'checkout_id' => $checkoutId,
                        'checkout_url' => $checkoutPageUrl,
                        'redirect_immediately' => true,
                        'warning' => 'Checkout ID expires quickly - redirect immediately',
                        'instruction' => 'Redirect user to checkout_url immediately. Freecharge will handle cookie/session.',
                        'response' => $responseBody
                    ], 200);
                } else {
                    // For non-JSON requests, redirect directly to checkout page
                    // Freecharge will handle cookie/session when user accesses the page
                    return redirect($checkoutPageUrl);
                }
            } else {
                // Error response
                $errorMessage = $responseBody['statusMessage'] ?? 'Unknown error';
                $errorCode = $responseBody['statusCode'] ?? 'UNKNOWN';
                
                return response()->json([
                    'status' => false,
                    'message' => 'Freecharge checkout failed: ' . $errorMessage,
                    'error' => $errorCode,
                    'error_details' => $responseBody
                ], $statusCode !== 200 ? $statusCode : 400);
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Freecharge V3: Connection error', [
                'txnid' => $trn,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Freecharge V3: Error initiating checkout', [
                'txnid' => $trn,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Initiate PayIn - Checkout API
     * POST /payment/v1/checkout
     * Content-Type: application/x-www-form-urlencoded
     * 
     * ⚠️ DEPRECATED: This method uses SERVER-TO-SERVER call which causes SESSION_EXPIRED errors
     * ✅ USE INSTEAD: /payload/{trn} endpoint for CLIENT-TO-SERVER submission
     * The frontend should get payload from /payload/{trn} and POST directly to Freecharge
     */
    public function initiatePayin(Request $request, $trn)
    {
        try {
            $pr = Payment_request::where('transaction_id', $trn)->first();
            if (!$pr) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }
            
            $merchantTxnId = $trn;
            $amount = (float)$pr->amount;
            $callbackUrl = url('/api/gateway/freecharge/callback');
            $redirectUrl = url('/payment-success?txn=' . $merchantTxnId);
            
            // Generate fresh timestamp RIGHT BEFORE request
            $timestamp = time();
            
            // Build request data (as per documentation)
            $requestData = [
                'merchantId' => self::MERCHANT_ID,
                'callbackUrl' => $callbackUrl,
                'redirectUrl' => $redirectUrl, // User redirect after payment
                'merchantTxnId' => $merchantTxnId,
                'merchantTxnAmount' => number_format($amount, 1, '.', ''),
                'currency' => 'INR',
                'timestamp' => $timestamp,
            ];
            
            // Generate signature
            $signature = $this->generateCheckoutSignature($requestData);
            $requestData['signature'] = $signature;
            
            // Get base URL
            $baseUrl = $this->getBaseUrl();
            $checkoutUrl = $baseUrl . '/payment/v1/checkout';
            
            // Build URL-encoded request body (exact format sent to Freecharge)
            $formDataString = http_build_query($requestData);
            
            // Log complete request details
            Log::info('Freecharge: Complete Request Details', [
                'txnid' => $merchantTxnId,
                'url' => $checkoutUrl,
                'method' => 'POST',
                'content_type' => 'application/x-www-form-urlencoded',
                'request_data' => $requestData,
                'request_body_url_encoded' => $formDataString,
                'request_body_decoded' => [
                    'merchantId' => $requestData['merchantId'],
                    'callbackUrl' => $requestData['callbackUrl'],
                    'merchantTxnId' => $requestData['merchantTxnId'],
                    'merchantTxnAmount' => $requestData['merchantTxnAmount'],
                    'currency' => $requestData['currency'],
                    'timestamp' => $requestData['timestamp'],
                    'signature' => $requestData['signature']
                ],
                'signature_preview' => substr($signature, 0, 20) . '...',
                'timestamp' => $timestamp,
                'current_unix_time' => time()
            ]);
            
            // Also save to database for debugging
            try {
                $log = new Logs();
                $log->uniqueid = 'FREECHARGE_REQUEST_BODY';
                $log->value = json_encode([
                    'url' => $checkoutUrl,
                    'request_body' => $requestData,
                    'url_encoded' => $formDataString,
                    'timestamp' => $timestamp
                ], JSON_PRETTY_PRINT);
                $log->data1 = $merchantTxnId;
                $log->data2 = $timestamp;
                $log->save();
            } catch (\Exception $e) {
                // Ignore log errors
            }
            
            // Send POST request to Freecharge
            
            try {
                // DO NOT follow redirects - get the redirect URL immediately
                $response = Http::asForm()
                    ->timeout(30)
                    ->withOptions([
                        'allow_redirects' => false, // CRITICAL: Don't follow redirects
                        'connect_timeout' => 10,
                        'verify' => true
                    ])
                    ->post($checkoutUrl, $requestData);
                
                $statusCode = $response->status();
                $locationHeader = $response->header('Location');
                $responseBody = $response->body();
                
                Log::info('Freecharge: Response received', [
                    'txnid' => $merchantTxnId,
                    'status' => $statusCode,
                    'location' => $locationHeader,
                    'body_length' => strlen($responseBody)
                ]);
                
                // Check for redirect (307, 302, etc.)
                if (($statusCode == 307 || $statusCode == 302 || $statusCode == 301) && $locationHeader) {
                    // Extract full URL from Location header
                    $redirectUrl = $locationHeader;
                    
                    // If relative URL, make it absolute
                    if (strpos($redirectUrl, 'http') !== 0) {
                        $redirectUrl = $baseUrl . $redirectUrl;
                    }
                    
                    // Extract checkout ID
                    $checkoutId = null;
                    if (preg_match('/\/rco\/checkout\/(CO\d+)/', $redirectUrl, $matches)) {
                        $checkoutId = $matches[1];
                    }
                    
                    Log::info('Freecharge: Redirect URL extracted', [
                        'txnid' => $merchantTxnId,
                        'redirect_url' => $redirectUrl,
                        'checkout_id' => $checkoutId
                    ]);
                    
                    // Return redirect URL immediately - frontend must redirect NOW
                    $request = request();
                    if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Freecharge payment page ready - redirect IMMEDIATELY',
                            'url' => $redirectUrl,
                            'payment_url' => $redirectUrl,
                            'checkout_id' => $checkoutId,
                            'redirect_immediately' => true,
                            'warning' => 'URL expires in seconds - redirect NOW',
                            'critical' => 'Do NOT delay - redirect within 1 second',
                            'request_body_sent' => [
                                'url_encoded' => http_build_query($requestData),
                                'decoded' => $requestData
                            ]
                        ], 200);
                    } else {
                        // Direct HTTP redirect
                        return redirect($redirectUrl);
                    }
                }
                
                // Check for HTML response (if redirects were followed somehow)
                if (strpos($responseBody, '<html') !== false || strpos($responseBody, '<!DOCTYPE') !== false) {
                    // Extract checkout ID from HTML if present
                    $checkoutId = null;
                    if (preg_match('/\/rco\/checkout\/(CO\d+)/', $responseBody, $matches)) {
                        $checkoutId = $matches[1];
                    }
                    
                    // Check for SESSION_EXPIRED error
                    if (strpos($responseBody, 'SESSION_EXPIRED') !== false || 
                        strpos($responseBody, 'session-expired') !== false) {
                        Log::error('Freecharge: SESSION_EXPIRED in response', [
                            'txnid' => $merchantTxnId,
                            'timestamp' => $timestamp
                        ]);
                        
                        return response()->json([
                            'status' => false,
                            'message' => 'Freecharge session expired. Please try again.',
                            'error' => 'SESSION_EXPIRED',
                            'suggestion' => 'Generate a new transaction ID and try immediately',
                            'timestamp' => $timestamp
                        ], 400);
                    }
                    
                    // Return HTML directly for immediate rendering
                    $request = request();
                    if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Freecharge payment page ready',
                            'html' => $responseBody,
                            'checkout_id' => $checkoutId,
                            'render_html_directly' => true,
                            'redirect_immediately' => true,
                            'warning' => 'URL expires in seconds - redirect NOW'
                        ], 200);
                    } else {
                        // Return HTML directly
                        return response($responseBody)->header('Content-Type', 'text/html');
                    }
                }
                
                // Try to parse as JSON
                $responseData = json_decode($responseBody, true);
                if ($responseData) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unexpected JSON response from Freecharge',
                        'data' => $responseData
                    ], 400);
                }
                
                // Unknown response format
                return response()->json([
                    'status' => false,
                    'message' => 'Unexpected response format from Freecharge',
                    'response_preview' => substr($responseBody, 0, 200)
                ], 400);
                
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Freecharge: Connection error', [
                    'txnid' => $merchantTxnId,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'status' => false,
                    'message' => 'Connection timeout. Please try again.',
                    'error' => 'CONNECTION_TIMEOUT'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Freecharge: PayIn error', [
                'txnid' => $trn ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle Callback from Freecharge
     * POST /api/gateway/freecharge/callback
     */
    public function handleCallback(Request $request)
    {
        try {
            $data = $request->all();
            
            // Check if this is a user redirect (GET request with transaction ID)
            // Freecharge may redirect users here after payment
            if ($request->isMethod('GET')) {
                $merchantTxnId = $request->input('merchantTxnId') ?? $request->input('txn') ?? $request->input('transaction_id');
                
                if ($merchantTxnId) {
                    $pr = Payment_request::where('transaction_id', $merchantTxnId)->first();
                    
                    if ($pr) {
                        // Check payment status and redirect accordingly
                        if ($pr->status == 1) {
                            // Payment successful
                            return $this->redirectToSuccessPage($pr, [
                                'txn' => $merchantTxnId,
                                'amount' => $pr->amount,
                            ]);
                        } elseif ($pr->status == 2) {
                            // Payment failed
                            $reason = $pr->data2 ? json_decode($pr->data2, true)['statusMessage'] ?? 'Payment failed' : 'Payment failed';
                            return $this->redirectToFailurePage($pr, [
                                'txn' => $merchantTxnId,
                                'amount' => $pr->amount,
                                'reason' => $reason,
                            ]);
                        } else {
                            // Payment pending - wait a moment and check again, or show pending
                            return $this->redirectToSuccessPage($pr, [
                                'txn' => $merchantTxnId,
                                'amount' => $pr->amount,
                                'status' => 'pending',
                            ]);
                        }
                    } else {
                        // Transaction not found, redirect to failed page
                        return $this->redirectToFailurePage(null, [
                            'txn' => $merchantTxnId,
                            'reason' => 'Transaction not found',
                        ]);
                    }
                }
            }
            
            // Log callback
            Log::info('Freecharge: Callback received', [
                'data_keys' => array_keys($data),
                'merchantTxnId' => $data['merchantTxnId'] ?? 'N/A',
                'method' => $request->method()
            ]);
            
            // Save callback log
            try {
                $log = new Logs();
                $log->uniqueid = 'FREECHARGE_CALLBACK';
                $log->value = json_encode($data);
                $log->data1 = $data['merchantTxnId'] ?? '';
                $log->data2 = $data['txnReferenceId'] ?? '';
                $log->save();
            } catch (\Exception $e) {
                Log::error('Freecharge: Failed to save callback log', ['error' => $e->getMessage()]);
            }
            
            // Verify signature
            $receivedSignature = $data['signature'] ?? '';
            $generatedSignature = $this->generateCallbackSignature($data);
            
            if (!hash_equals($generatedSignature, $receivedSignature)) {
                Log::error('Freecharge: Invalid callback signature', [
                    'received' => $receivedSignature,
                    'generated' => $generatedSignature,
                    'merchantTxnId' => $data['merchantTxnId'] ?? 'N/A'
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid signature'
                ], 400);
            }
            
            // Extract callback data
            $merchantTxnId = $data['merchantTxnId'] ?? '';
            $statusCode = $data['statusCode'] ?? '';
            $statusMessage = $data['statusMessage'] ?? '';
            $txnReferenceId = $data['txnReferenceId'] ?? '';
            $amount = $data['amount'] ?? 0;
            
            // Find payment request
            $pr = Payment_request::where('transaction_id', $merchantTxnId)->first();
            if (!$pr) {
                Log::error('Freecharge: Payment request not found', ['txnid' => $merchantTxnId]);
                // If this is a user redirect, redirect to failure page; otherwise return JSON
                if ($request->isMethod('GET') || ($request->isMethod('POST') && !$request->expectsJson() && !$request->ajax())) {
                    return $this->redirectToFailurePage(null, [
                        'txn' => $merchantTxnId,
                        'reason' => 'Transaction not found',
                    ]);
                }
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }
            
            // Check if this is a user browser redirect (not a server callback)
            // User redirects typically come as GET requests or POST from browser
            $isUserRedirect = $request->isMethod('GET') || 
                             ($request->isMethod('POST') && !$request->expectsJson() && !$request->ajax());
            
            // Process based on status
            if ($statusCode === 'SPG-0000') {
                // SUCCESS
                $pr->status = 1;
                $pr->data1 = $txnReferenceId;
                $pr->data2 = json_encode($data);
                
                // Store callback payload for MerchantCallbackService
                $pr->callback_payload = $data;
                
                $pr->save();
                
                // Update wallet and transaction records
                $iNITAEAmount = floatval($pr->amount);
                
                if ($pr->data3 == 1) {
                    // Payin transaction - add to payin wallet
                    $finalamount = $iNITAEAmount - $pr->tax;
                    addtransaction($pr->userid, 'payin', 'credit', $finalamount, 'Freecharge Payment', 1, $txnReferenceId);
                    addwallet($pr->userid, $finalamount, '+', 'payin');
                } else {
                    // Wallet transaction - add to main wallet
                    addtransaction($pr->userid, 'add_fund', 'credit', $iNITAEAmount, 'Freecharge Payment', 1, $txnReferenceId);
                    addwallet($pr->userid, $iNITAEAmount, '+', 'wallet');
                }
                
                Log::info('Freecharge: Payment successful', [
                    'txnid' => $merchantTxnId,
                    'reference' => $txnReferenceId,
                    'amount' => $amount,
                    'wallet_type' => $pr->data3 == 1 ? 'payin' : 'wallet',
                    'is_user_redirect' => $isUserRedirect
                ]);
                
                // If user redirect, send to success page; otherwise return JSON for server callback
                if ($isUserRedirect) {
                    return $this->redirectToSuccessPage($pr, [
                        'txn' => $merchantTxnId,
                        'amount' => $amount,
                        'utr' => $txnReferenceId,
                        'status' => 'success',
                    ]);
                }
                
                return response()->json(['status' => 'success', 'message' => 'Payment successful']);
                
            } else {
                // FAILED or PENDING
                $pr->status = 2; // Failed
                $pr->data1 = $txnReferenceId ?? '';
                $pr->data2 = json_encode($data);
                $pr->save();
                
                Log::info('Freecharge: Payment failed/pending', [
                    'txnid' => $merchantTxnId,
                    'statusCode' => $statusCode,
                    'statusMessage' => $statusMessage,
                    'is_user_redirect' => $isUserRedirect
                ]);
                
                // If user redirect, send to failed page; otherwise return JSON for server callback
                if ($isUserRedirect) {
                    return $this->redirectToFailurePage($pr, [
                        'txn' => $merchantTxnId,
                        'amount' => $amount,
                        'reason' => $statusMessage,
                    ]);
                }
                
                return response()->json([
                    'status' => 'error',
                    'message' => $statusMessage,
                    'statusCode' => $statusCode
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Freecharge: Callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Get transaction ID from request if available
            $txnid = $request->input('merchantTxnId', 'unknown');
            $amount = $request->input('amount', '0');
            
            // If this is a user redirect, redirect to failure page; otherwise return JSON
            $isUserRedirect = $request->isMethod('GET') || 
                             ($request->isMethod('POST') && !$request->expectsJson() && !$request->ajax());
            
            if ($isUserRedirect) {
                $pr = null;
                if ($txnid !== 'unknown') {
                    $pr = Payment_request::where('transaction_id', $txnid)->first();
                }
                return $this->redirectToFailurePage($pr, [
                    'txn' => $txnid,
                    'amount' => $amount,
                    'reason' => 'An error occurred while processing the payment',
                ]);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Callback processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check transaction status
     * POST /payment/v5/txn/status
     */
    public function checkStatus(Request $request)
    {
        try {
            $merchantTxnId = $request->input('merchantTxnId');
            $txnReferenceId = $request->input('txnReferenceId');
            
            if (!$merchantTxnId) {
                return response()->json(['status' => false, 'message' => 'merchantTxnId required'], 400);
            }
            
            // Build request
            $requestData = [
                'merchantId' => self::MERCHANT_ID,
                'merchantTxnId' => $merchantTxnId,
            ];
            
            if ($txnReferenceId) {
                $requestData['txnReferenceId'] = $txnReferenceId;
            }
            
            // Generate signature (alphabetical order)
            ksort($requestData);
            $concatenatedString = implode('', array_values($requestData));
            $concatenatedString .= $this->getSecretKey();
            $signature = hash('sha256', $concatenatedString);
            $requestData['signature'] = $signature;
            
            // Send request
            $baseUrl = $this->getBaseUrl();
            $response = Http::asJson()
                ->timeout(30)
                ->post($baseUrl . '/payment/v5/txn/status', $requestData);
            
            return response()->json(json_decode($response->body(), true));
            
        } catch (\Exception $e) {
            Log::error('Freecharge: Status check error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Initiate refund
     * POST /payment/v3/refund
     */
    public function initiateRefund(Request $request)
    {
        try {
            $merchantRefundTxnId = $request->input('merchantRefundTxnId');
            $txnReferenceId = $request->input('txnReferenceId');
            $refundAmount = $request->input('refundAmount');
            $refundType = $request->input('refundType', 'OFFLINE');
            
            if (!$merchantRefundTxnId || !$txnReferenceId || !$refundAmount) {
                return response()->json([
                    'status' => false,
                    'message' => 'merchantRefundTxnId, txnReferenceId, and refundAmount are required'
                ], 400);
            }
            
            // Build request
            $requestData = [
                'merchantId' => self::MERCHANT_ID,
                'merchantRefundTxnId' => $merchantRefundTxnId,
                'txnReferenceId' => $txnReferenceId,
                'refundAmount' => number_format((float)$refundAmount, 1, '.', ''),
                'currency' => 'INR',
                'refundType' => $refundType
            ];
            
            // Generate signature (alphabetical order)
            ksort($requestData);
            $concatenatedString = implode('', array_values($requestData));
            $concatenatedString .= $this->getSecretKey();
            $signature = hash('sha256', $concatenatedString);
            $requestData['signature'] = $signature;
            
            // Send request
            $baseUrl = $this->getBaseUrl();
            $response = Http::asJson()
                ->timeout(30)
                ->post($baseUrl . '/payment/v3/refund', $requestData);
            
            return response()->json(json_decode($response->body(), true));
            
        } catch (\Exception $e) {
            Log::error('Freecharge: Refund error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Refund failed: ' . $e->getMessage()
            ], 500);
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

    /**
     * Redirect user to merchant-provided failure page if configured.
     */
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
                Log::info('Freecharge: Redirect URL set from user config', [
                    'txnid' => $paymentRequest->transaction_id,
                    'userid' => $paymentRequest->userid,
                    'redirect' => $user->payin_success_redirect
                ]);
            } else {
                Log::info('Freecharge: No redirect URL configured', [
                    'txnid' => $paymentRequest->transaction_id,
                    'userid' => $paymentRequest->userid,
                    'user_found' => $user !== null,
                    'redirect_configured' => $user && !empty($user->payin_success_redirect)
                ]);
            }
        } else {
            Log::info('Freecharge: No payment request found for redirect', [
                'params' => $params
            ]);
        }

        return redirect('/payment-failed?' . http_build_query($query));
    }
}
