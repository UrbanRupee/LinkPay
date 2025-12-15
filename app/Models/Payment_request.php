<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\MerchantCallbackService;
use App\Services\PayinFeeService;

class Payment_request extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'transaction_id',
        'userid',
        'amount',
        'tax',
        'data1',
        'data2',
        'data3',
        'data4',
        'data5',
        'data6',
        'status',
        'mobile',
        'name',
        'callbackurl',
        'callback_payload'
    ];

    protected $casts = [
        'callback_payload' => 'array',
    ];

    protected static function booted()
    {
        static::updated(function (Payment_request $paymentRequest) {
            $changes = $paymentRequest->getChanges();

            $statusChanged = array_key_exists('status', $changes);
            $payloadChanged = array_key_exists('callback_payload', $changes);

            if ($statusChanged) {
                $originalStatus = $paymentRequest->getOriginal('status');
                $currentStatus = $paymentRequest->status;

                if ($originalStatus != $currentStatus) {
                    if ($currentStatus == 1) {
                        MerchantCallbackService::dispatchSuccess($paymentRequest);
                    } elseif ($currentStatus == 2) {
                        MerchantCallbackService::dispatchFailure($paymentRequest);
                    }
                }
            }

            if (($statusChanged || $payloadChanged) && (int) $paymentRequest->status === 1) {
                PayinFeeService::syncModeFees($paymentRequest);
            }
        });
    }
}
