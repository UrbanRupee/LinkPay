<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'userid',
        'category',
        'type',
        'amount',
        'status',
        'data1',
        'data2',
        'data3',
        'data4',
        'data5',
        'data6'
    ];
}
