<?php

namespace App\Services;

use App\Http\Controllers\Gateway\AuroPay as AuroPayGateway;
use App\Models\Payment_request;
use App\Models\user;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MerchantCallbackService
{
    public static function dispatchSuccess(Payment_request $paymentRequest): void
    {
        self::refreshGatewayMetadata($paymentRequest);

        $callbackUrl = self::determineMerchantCallbackUrl($paymentRequest);

        if (empty($callbackUrl)) {
            return;
        }

        $payloadSource = self::normalizePayload($paymentRequest);

        $payload = [
            'status' => 'success',
            'client_txn_id' => $paymentRequest->transaction_id,
            'txn_id' => $paymentRequest->transaction_id,
            'txnId' => $paymentRequest->transaction_id,
            'utr' => self::firstNonEmpty(
                $paymentRequest->data4,
                Arr::get($payloadSource, 'traceNumber'),
                Arr::get($payloadSource, 'processorRefId'),
                Arr::get($payloadSource, 'utr'),
                Arr::get($payloadSource, 'UTR'),
                $paymentRequest->data1,
                $paymentRequest->data2,
                Arr::get($payloadSource, 'bank_ref_num'),
                Arr::get($payloadSource, 'auropay_status.traceNumber')
            ),
            'amount' => (string) $paymentRequest->amount,
        ];

        $payload = array_merge($payload, self::additionalGatewayFields($paymentRequest, $payloadSource));

        self::post($callbackUrl, $payload, $paymentRequest, 'success');
    }

    public static function dispatchFailure(Payment_request $paymentRequest): void
    {
        $callbackUrl = self::determineMerchantCallbackUrl($paymentRequest);

        if (empty($callbackUrl)) {
            return;
        }

        $payload = [
            'status' => 'failed',
            'client_txn_id' => $paymentRequest->transaction_id,
            'txn_id' => $paymentRequest->transaction_id,
            'txnId' => $paymentRequest->transaction_id,
            'utr' => '00',
            'amount' => (string) $paymentRequest->amount,
        ];

        if (!empty($paymentRequest->data2)) {
            $payload['reason'] = $paymentRequest->data2;
        }

        $payload = array_merge($payload, self::additionalGatewayFields($paymentRequest));

        self::post($callbackUrl, $payload, $paymentRequest, 'failed');
    }

    protected static function post(string $url, array $payload, Payment_request $paymentRequest, string $status): void
    {
        try {
            // Validate URL before making request
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                Log::warning('Merchant Callback Invalid URL', [
                    'transaction_id' => $paymentRequest->transaction_id,
                    'userid' => $paymentRequest->userid,
                    'url' => $url,
                ]);
                return;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Merchant-Callback/1.0',
                ])
                ->post($url, $payload);

            $responseStatus = $response->status();
            $responseBody = $response->body();

            // Log success or failure
            if ($responseStatus >= 200 && $responseStatus < 300) {
                Log::info('Merchant Callback Dispatched Successfully', [
                    'transaction_id' => $paymentRequest->transaction_id,
                    'userid' => $paymentRequest->userid,
                    'status' => $status,
                    'url' => $url,
                    'payload' => $payload,
                    'response_status' => $responseStatus,
                    'response_body' => $responseBody,
                ]);
            } else {
                Log::warning('Merchant Callback Received Non-2xx Response', [
                    'transaction_id' => $paymentRequest->transaction_id,
                    'userid' => $paymentRequest->userid,
                    'status' => $status,
                    'url' => $url,
                    'payload' => $payload,
                    'response_status' => $responseStatus,
                    'response_body' => $responseBody,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Merchant Callback Dispatch Failed', [
                'transaction_id' => $paymentRequest->transaction_id,
                'userid' => $paymentRequest->userid,
                'status' => $status,
                'url' => $url,
                'payload' => $payload,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected static function additionalGatewayFields(Payment_request $paymentRequest, ?array $payloadSource = null): array
    {
        $payload = $payloadSource ?? self::normalizePayload($paymentRequest);

        $mode = Arr::get($payload, 'mode') ?? Arr::get($payload, 'payment_source');
        $upi = Arr::get($payload, 'upi_va') ?? Arr::get($payload, 'vpa');

        $extras = [];
        if (!empty($mode)) {
            $extras['mode'] = $mode;
        }
        if (!empty($upi)) {
            $extras['upi_va'] = $upi;
        }

        return $extras;
    }

    protected static function firstNonEmpty(...$values): string
    {
        foreach ($values as $value) {
            if (!empty($value)) {
                return $value;
            }
        }

        return '';
    }

    protected static function determineMerchantCallbackUrl(Payment_request $paymentRequest): ?string
    {
        $candidate = null;

        if (!empty($paymentRequest->callbackurl)) {
            $candidate = trim($paymentRequest->callbackurl);
        }

        $user = user::where('userid', $paymentRequest->userid)->first();
        if ($user && !empty($user->callback)) {
            $candidate = trim($user->callback);
        }

        if (empty($candidate)) {
            return null;
        }

        return $candidate;
    }

    protected static function normalizePayload(Payment_request $paymentRequest): array
    {
        $payload = $paymentRequest->callback_payload;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            } else {
                $payload = [];
            }
        } elseif (!is_array($payload)) {
            $payload = [];
        }

        return $payload;
    }

    protected static function refreshGatewayMetadata(Payment_request $paymentRequest): void
    {
        if (!in_array($paymentRequest->data6, ['29', 29], true)) {
            return;
        }

        $transactionId = $paymentRequest->data1 ?: $paymentRequest->transaction_id;
        if (!$transactionId) {
            return;
        }

        try {
            $gateway = app(AuroPayGateway::class);
            $status = $gateway->checkTransactionStatus($transactionId);

            if (!is_array($status) || empty($status)) {
                return;
            }

            $updates = [];

            $traceNumber = Arr::get($status, 'traceNumber') ?: Arr::get($status, 'processorRefId');
            if (!empty($traceNumber) && $paymentRequest->data4 !== $traceNumber) {
                $updates['data4'] = $traceNumber;
            }

            $processorName = Arr::get($status, 'processorName');
            if (!empty($processorName) && $paymentRequest->data2 !== $processorName) {
                $updates['data2'] = $processorName;
            }

            $gatewayTxn = Arr::get($status, 'transactionId');
            if (!empty($gatewayTxn) && $paymentRequest->data1 !== $gatewayTxn) {
                $updates['data1'] = $gatewayTxn;
            }

            $shouldSave = false;

            Payment_request::withoutEvents(function () use ($paymentRequest, $updates, $status, &$shouldSave) {
                if ($updates) {
                    $paymentRequest->fill($updates);
                    $shouldSave = true;
                }

                if (Schema::hasColumn($paymentRequest->getTable(), 'callback_payload')) {
                    $paymentRequest->callback_payload = $status;
                    $shouldSave = true;
                }

                if ($shouldSave && $paymentRequest->isDirty()) {
                    $paymentRequest->save();
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Merchant callback metadata refresh failed', [
                'transaction_id' => $paymentRequest->transaction_id,
                'userid' => $paymentRequest->userid,
                'error' => $e->getMessage(),
            ]);
        }
    }
}