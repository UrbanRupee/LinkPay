<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Logics_building;

class SwipePointe extends Controller
{
    protected $LogicsApi;

    public function __construct(Logics_building $LogicsApi)
    {
        $this->LogicsApi = $LogicsApi;
    }

    /**
     * Process SwipePointe card transaction
     */
    public function processCardTransaction(Request $request)
    {
        try {
             // Validation is already done in the main card_transaction function
            // IP whitelist check removed - admin panel transactions don't need IP restriction
            $userCardConfig = usercardconfig($request->userid);

            // Hardcoded SwipePointe credentials (as requested)
            $username = 'testsandbox@gmail.com';
            $password = 'UEMLXZ';
            $baseUrl = 'https://sandbox.swipepointe.com';
            
            // Step 1: Get authentication token
            $tokenResponse = $this->getToken($baseUrl, $username, $password);
            
            if ($tokenResponse['status'] !== 'success') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Authentication failed: ' . $tokenResponse['message']
                ], 401);
            }

            $secretKey = $tokenResponse['data']['token'];

            // Step 2: Process card charge
            $chargeResponse = $this->chargeCard($baseUrl, $secretKey, $request, $userCardConfig);
            
            return response()->json($chargeResponse);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'SwipePointe error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SwipePointe authentication token
     */
    private function getToken($baseUrl, $username, $password)
    {
        $url = $baseUrl . '/api/token';
        
        $data = [
            'username' => $username,
            'password' => $password
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            return [
                'status' => 'success',
                'data' => $responseData
            ];
        } else {
            return [
                'status' => 'failed',
                'message' => 'HTTP ' . $httpCode . ': ' . $response
            ];
        }
    }

    /**
     * Process SwipePointe card charge
     */
    private function chargeCard($baseUrl, $secretKey, $request, $userCardConfig)
    {
        $url = $baseUrl . '/api/charge';
        
        $data = [
            'amount' => $request->amount,
            'currency' => $request->currency,
            'reference' => $request->reference,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'cardName' => $request->cardName,
            'cardNumber' => $request->cardNumber,
            'cardCVV' => $request->cardCVV,
            'expMonth' => $request->expMonth,
            'expYear' => $request->expYear,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'ip_address' => $request->ip_address,
            'zip_code' => $request->zip_code,
            'state' => $request->state,
            'callback_url' => $request->callback_url
        ];

        // Add webhook_url if provided
        if ($request->has('webhook_url')) {
            $data['webhook_url'] = $request->webhook_url;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $secretKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            
            // Standardize response format
            if ($responseData['status'] === 'success') {
                            // Store card transaction in database using helper function
            $cardData = [
                'cardName' => $request->cardName,
                'cardNumber' => maskcardnumber($request->cardNumber), // Mask card number
                'expMonth' => $request->expMonth,
                'expYear' => $request->expYear,
                'currency' => $request->currency,
                'callback_url' => url('/api/gateway/swipepointe/callback'),
                'webhook_url' => url('/api/gateway/swipepointe/webhook'),
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip_code' => $request->zip_code,
                'ip_address' => $request->ip_address
            ];
                
                // Calculate fees
                $calculatedFees = calculatecardfees($request->amount, 'swipepointe');
                $userFees = ($request->amount * ($userCardConfig['card_percentage'] / 100)) + $userCardConfig['card_fixed_fee'];
                
                // Prepare response data with fees
                $responseDataWithFees = $responseData;
                $responseDataWithFees['data']['fees'] = $calculatedFees;
                $responseDataWithFees['data']['user_fees'] = $userFees;
                
                $transactionId = addcardtransaction(
                    $request->userid ?? 0, // You might want to get this from session/auth
                    'swipepointe',
                    $request->amount,
                    $request->currency,
                    $responseData['data']['reference'],
                    $responseData['data']['orderid'],
                    'success',
                    $cardData,
                    $responseDataWithFees
                );
                
                return [
                    'status' => 'success',
                    'message' => $responseData['message'],
                    'data' => [
                        'reference' => $responseData['data']['reference'],
                        'orderid' => $responseData['data']['orderid'],
                        'transaction_status' => $responseData['data']['transaction']['status'] ?? 'pending',
                        'redirect_link' => $responseData['data']['link'] ?? null,
                        'provider' => 'swipepointe',
                        'transaction_id' => $transactionId,
                        'fees' => calculatecardfees($request->amount, 'swipepointe'),
                        'user_fees' => ($request->amount * ($userCardConfig['card_percentage'] / 100)) + $userCardConfig['card_fixed_fee']
                    ]
                ];
            } else {
                // Store failed transaction
                $cardData = [
                    'cardName' => $request->cardName,
                    'cardNumber' => maskcardnumber($request->cardNumber),
                    'expMonth' => $request->expMonth,
                    'expYear' => $request->expYear,
                    'currency' => $request->currency,
                    'callback_url' => url('/api/gateway/swipepointe/callback'),
                    'webhook_url' => url('/api/gateway/swipepointe/webhook'),
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'zip_code' => $request->zip_code,
                    'ip_address' => $request->ip_address
                ];
                
                // Calculate fees for failed transaction
                $calculatedFees = calculatecardfees($request->amount, 'swipepointe');
                $userFees = ($request->amount * ($userCardConfig['card_percentage'] / 100)) + $userCardConfig['card_fixed_fee'];
                
                // Prepare response data with fees
                $responseDataWithFees = $responseData;
                $responseDataWithFees['data']['fees'] = $calculatedFees;
                $responseDataWithFees['data']['user_fees'] = $userFees;
                
                addcardtransaction(
                    $request->userid ?? 0,
                    'swipepointe',
                    $request->amount,
                    $request->currency,
                    $request->reference,
                    'FAILED_' . time(),
                    'failed',
                    $cardData,
                    $responseDataWithFees
                );
                
                return [
                    'status' => 'failed',
                    'message' => $responseData['message'] ?? 'Transaction failed',
                    'provider' => 'swipepointe'
                ];
            }
        } else {
            // Store failed transaction
            $cardData = [
                'cardName' => $request->cardName,
                'cardNumber' => maskcardnumber($request->cardNumber),
                'expMonth' => $request->expMonth,
                'expYear' => $request->expYear,
                'currency' => $request->currency,
                'callback_url' => url('/api/gateway/swipepointe/callback'),
                'webhook_url' => url('/api/gateway/swipepointe/webhook'),
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip_code' => $request->zip_code,
                'ip_address' => $request->ip_address
            ];
            
            // Calculate fees for HTTP error transaction
            $calculatedFees = calculatecardfees($request->amount, 'swipepointe');
            $userFees = ($request->amount * ($userCardConfig['card_percentage'] / 100)) + $userCardConfig['card_fixed_fee'];
            
            // Prepare response data with fees
            $errorResponseData = [
                'error' => 'HTTP ' . $httpCode . ': ' . $response,
                'data' => [
                    'fees' => $calculatedFees,
                    'user_fees' => $userFees
                ]
            ];
            
            addcardtransaction(
                $request->userid ?? 0,
                'swipepointe',
                $request->amount,
                $request->currency,
                $request->reference,
                'HTTP_ERROR_' . $httpCode,
                'failed',
                $cardData,
                $errorResponseData
            );
            
            return [
                'status' => 'failed',
                'message' => 'HTTP ' . $httpCode . ': ' . $response,
                'provider' => 'swipepointe'
            ];
        }
    }

    /**
     * Handle SwipePointe callback/webhook
     * This is called by SwipePointe after transaction processing
     */
    public function handleCallback(Request $request)
    {
        try {
            \Log::info('SwipePointe Callback Received', $request->all());
            
            // Validate callback data
            $reference = $request->input('reference');
            $orderId = $request->input('orderid');
            $status = $request->input('status');
            $transactionStatus = $request->input('transaction.status');
            $message = $request->input('message');
            
            if (!$reference || !$orderId) {
                \Log::error('SwipePointe Callback: Missing required fields', $request->all());
                return response()->json(['status' => 'error', 'message' => 'Missing required fields'], 400);
            }
            
            // Find the card transaction by reference
            $cardTransaction = \DB::table('card_transactions')
                ->where('reference', $reference)
                ->first();
            
            if (!$cardTransaction) {
                \Log::error('SwipePointe Callback: Transaction not found', ['reference' => $reference]);
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }
            
            // Update transaction status based on callback
            $updateData = [
                'status' => $status === 'success' ? 'success' : 'failed',
                'updated_at' => now()
            ];
            
            // If transaction has additional data, update it
            if ($request->has('transaction')) {
                $updateData['transaction_data'] = json_encode($request->input('transaction'));
            }
            
            // Update the transaction
            \DB::table('card_transactions')
                ->where('id', $cardTransaction->id)
                ->update($updateData);
            
            \Log::info('SwipePointe Callback: Transaction updated', [
                'id' => $cardTransaction->id,
                'reference' => $reference,
                'status' => $updateData['status']
            ]);
            
            // Return success response to SwipePointe
            return response()->json(['status' => 'success', 'message' => 'Callback processed']);
            
        } catch (\Exception $e) {
            \Log::error('SwipePointe Callback Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }

    /**
     * Handle SwipePointe webhook (POST method)
     * Alternative to callback for webhook-based notifications
     */
    public function handleWebhook(Request $request)
    {
        try {
            \Log::info('SwipePointe Webhook Received', $request->all());
            
            // Process the same way as callback
            return $this->handleCallback($request);
            
        } catch (\Exception $e) {
            \Log::error('SwipePointe Webhook Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }
}
