<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'userid',
        'transaction_id',
        'amount',
        'account_no', // Correct column name
        'account_number',
        'ifsc',
        'ifsc_code',
        'holder_name', // Correct column name
        'name',
        'status',
        'data1',
        'data2',
        'data3',
        'data4',
        'data5',
        'payout_id'
    ];
}
