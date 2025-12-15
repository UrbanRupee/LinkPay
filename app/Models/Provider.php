<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'provider_name',
        'gateway_id',
        'gateway_type',
        'api_key',
        'api_secret',
        'merchant_id',
        'environment',
        'callback_url',
        'webhook_url',
        'status',
        'priority',
        'notes',
        // Legacy fields (keep for backward compatibility)
        'name',
        'location',
        'service_type',
        'url',
        'commercial_mdr',
        'cards',
        'apms',
        'bank_transfer',
        'in',
        'out',
        'settlement_timeline',
        'settlement_mode',
        'contact_spoc',
        'contact_number',
        'risk_and_blacklisting',
    ];

    protected $casts = [
        'cards' => 'boolean',
        'apms' => 'boolean',
        'bank_transfer' => 'boolean',
        'in' => 'boolean',
        'out' => 'boolean',
        'status' => 'integer',
        'priority' => 'integer',
    ];
}
