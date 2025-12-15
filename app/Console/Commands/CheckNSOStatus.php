<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment_request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\Wallet;

class CheckNSOStatus extends Command
{
    protected $signature = 'nso:check-status';
    protected $description = 'Check pending NSO transactions and update their status';

    public function handle()
    {
        $this->info('Checking pending NSO transactions...');

        // Find pending NSO transactions (data3 contains UUID or intent_)
        $pendingTransactions = Payment_request::where('status', 0)
            ->where(function($query) {
                $query->where('data3', 'like', 'intent_%')
                      ->orWhere('data3', 'like', '%-%-%-%-%');
            })
            ->whereNotNull('data1') // Must have NSO transaction ID
            ->where('created_at', '>=', now()->subHours(24)) // Only check recent transactions
            ->get();

        $this->info("Found {$pendingTransactions->count()} pending NSO transactions");

        foreach ($pendingTransactions as $transaction) {
            $this->checkTransactionStatus($transaction);
        }

        $this->info('NSO status check completed');
    }

    private function checkTransactionStatus($paymentRequest)
    {
        try {
            $nsoTransactionId = $paymentRequest->data1;
            
            // Check NSO API for transaction status
            $statusData = [
                'transaction_id' => $nsoTransactionId
            ];

            $response = Http::withHeaders([
                'X-API-Key' => 'vk_56ce985d0ca48c4b36b8ca640c9142daf5e0250622cdfb2e39f82056c1426f47',
                'Content-Type' => 'application/json'
            ])->timeout(30)->post('https://merchant.odinpe.com/api/transactions/status', $statusData);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status'])) {
                $status = $responseData['status'];
                $amount = $responseData['amount'] ?? $paymentRequest->amount;
                $upiReference = $responseData['payment_intent']['upi_id']['upi_id'] ?? null;
                $transactionTime = $responseData['transaction_time'] ?? null;

                if (strtolower($status) === 'success' || strtolower($status) === 'completed') {
                    // Validate UTR exists before marking as successful
                    if (empty($upiReference) || $upiReference === null) {
                        $this->warn("⚠️  {$paymentRequest->transaction_id} - Completed without UTR, keeping as pending");
                        \Log::warning('NSO Payment Completed Without UTR (Cron)', [
                            'transaction_id' => $paymentRequest->transaction_id,
                            'status' => $status,
                            'message' => 'Odinpe returned completed status without UTR - keeping as pending'
                        ]);
                        return; // Don't process without UTR
                    }
                    
                    // Update payment request to success
                    $paymentRequest->update([
                        'status' => 1,
                        'data4' => $upiReference,
                        'data6' => $transactionTime
                    ]);

                    // Credit user's payin wallet with NET amount (amount - tax) to match other gateways
                    $netAmount = $paymentRequest->amount - $paymentRequest->tax;
                    addwallet($paymentRequest->userid, $netAmount, '+', 'payin');
                    
                    \Log::info('NSO Wallet Credited (Cron)', [
                        'transaction_id' => $paymentRequest->transaction_id,
                        'userid' => $paymentRequest->userid,
                        'gross_amount' => $paymentRequest->amount,
                        'tax' => $paymentRequest->tax,
                        'net_amount' => $netAmount,
                        'utr' => $upiReference
                    ]);

                    // Create transaction record with NET amount
                    Transaction::create([
                        'userid' => $paymentRequest->userid,
                        'amount' => $netAmount,
                        'type' => 'credit',
                        'description' => 'NSO PayIn Success',
                        'status' => 'success'
                    ]);

                    // Send callback to merchant
                    if ($paymentRequest->callbackurl) {
                        $callbackData = [
                            'status' => 'success',
                            'client_txn_id' => $paymentRequest->client_txn_id,
                            'amount' => $paymentRequest->amount,
                            'utr' => $upiReference,
                            'vendor_id' => $paymentRequest->data3
                        ];

                        try {
                            Http::timeout(10)->post($paymentRequest->callbackurl, $callbackData);
                        } catch (\Exception $callbackError) {
                            \Log::error('NSO Merchant Callback Failed', [
                                'transaction_id' => $paymentRequest->transaction_id,
                                'callback_url' => $paymentRequest->callbackurl,
                                'error' => $callbackError->getMessage()
                            ]);
                        }
                    }

                    $this->info("✅ Updated {$paymentRequest->transaction_id} to success");
                } else {
                    $this->info("⏳ {$paymentRequest->transaction_id} still pending");
                }
            } else {
                $this->info("❌ Could not check status for {$paymentRequest->transaction_id}");
            }

        } catch (\Exception $e) {
            $this->error("Error checking {$paymentRequest->transaction_id}: " . $e->getMessage());
        }
    }
}

