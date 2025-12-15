<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardTransaction extends Model
{
    use HasFactory;

    protected $table = 'card_transactions';

    protected $fillable = [
        'userid',
        'reference',
        'orderid',
        'amount',
        'currency',
        'status',
        'provider',
        'card_name',
        'card_number_masked',
        'card_expiry',
        'card_type',
        'transaction_type',
        'fees',
        'user_fees',
        'redirect_link',
        'gateway_response',
        'ip_address',
        'callback_url',
        'webhook_url',
        'customer_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'user_fees' => 'decimal:2',
        'gateway_response' => 'array',
        'customer_details' => 'array'
    ];

    /**
     * Get the user that owns the card transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    /**
     * Scope for successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific provider
     */
    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userid)
    {
        return $query->where('userid', $userid);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get formatted fees with currency
     */
    public function getFormattedFeesAttribute()
    {
        return $this->currency . ' ' . number_format($this->user_fees, 2);
    }

    /**
     * Check if transaction is 3D Secure
     */
    public function getIs3DSecureAttribute()
    {
        return $this->transaction_type === '3D';
    }

    /**
     * Check if transaction needs redirect
     */
    public function getNeedsRedirectAttribute()
    {
        return !empty($this->redirect_link);
    }
}
