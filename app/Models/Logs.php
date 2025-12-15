<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'userid',
        'transaction_id',
        'log',
        'type',
        'request',
        'response',
        'status',
        'uniqueid',
        'value',
        'data1',
        'data2',
        'data3',
        'data4',
        'data5'
    ];
}
