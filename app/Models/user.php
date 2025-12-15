<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class user extends Model
{
    use HasFactory;
    public function payins()
    {
        return $this->hasMany(Payment_request::class, 'userid', 'userid');
    }
    
    public function payouts()
    {
        return $this->hasMany(PayoutRequest::class, 'userid', 'userid');
    }
    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'userid', 'userid');
    }
    public function scopeWithLedgerStats($query, $from, $to)
{
    $from = Carbon::parse($from)->startOfDay();
    $to   = Carbon::parse($to)->endOfDay();

    /* ----  pay‑in aggregate  ---------------------------------------------- */
    $payin = DB::table('payment_requests')
        ->selectRaw("
            userid,
            COUNT(CASE WHEN status=0 THEN 1 END)                  AS payin_pending_cnt,
            SUM(  CASE WHEN status=0 THEN amount END)             AS payin_pending_sum,

            COUNT(CASE WHEN status=1 THEN 1 END)                  AS payin_success_cnt,
            SUM(  CASE WHEN status=1 THEN amount END)             AS payin_success_sum,

            COUNT(CASE WHEN status=2 THEN 1 END)                  AS payin_failed_cnt,
            SUM(  CASE WHEN status=2 THEN amount END)             AS payin_failed_sum
        ")
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('userid');

    /* ----  pay‑out aggregate  --------------------------------------------- */
    $payout = DB::table('payout_requests')
        ->selectRaw("
            userid,
            COUNT(CASE WHEN status=0 THEN 1 END)                  AS payout_pending_cnt,
            SUM(  CASE WHEN status=0 THEN amount + tax END)       AS payout_pending_sum,

            COUNT(CASE WHEN status=1 THEN 1 END)                  AS payout_success_cnt,
            SUM(  CASE WHEN status=1 THEN amount + tax END)       AS payout_success_sum,

            COUNT(CASE WHEN status=2 THEN 1 END)                  AS payout_failed_cnt,
            SUM(  CASE WHEN status=2 THEN amount + tax END)       AS payout_failed_sum
        ")
        ->whereBetween('created_at', [$from, $to])
        ->groupBy('userid');

    /* ----  stitch both sub‑queries onto users ----------------------------- */
    return $query
        ->leftJoinSub($payin,  'pin',  'pin.userid',  '=', 'users.userid')
        ->leftJoinSub($payout, 'pout', 'pout.userid', '=', 'users.userid')
        ->addSelect([
            'users.*',

            DB::raw('COALESCE(pin.payin_pending_cnt,  0) AS TotalPayinPending'),
            DB::raw('COALESCE(pin.payin_pending_sum,  0) AS SumPayinPending'),
            DB::raw('COALESCE(pin.payin_success_cnt,  0) AS TotalPayinSuccess'),
            DB::raw('COALESCE(pin.payin_success_sum,  0) AS SumPayinSuccess'),
            DB::raw('COALESCE(pin.payin_failed_cnt,   0) AS TotalPayinFailed'),
            DB::raw('COALESCE(pin.payin_failed_sum,   0) AS SumPayinFailed'),

            DB::raw('COALESCE(pout.payout_pending_cnt, 0) AS TotalPayoutPending'),
            DB::raw('COALESCE(pout.payout_pending_sum, 0) AS SumPayoutPending'),
            DB::raw('COALESCE(pout.payout_success_cnt, 0) AS TotalPayoutSuccess'),
            DB::raw('COALESCE(pout.payout_success_sum, 0) AS SumPayoutSuccess'),
            DB::raw('COALESCE(pout.payout_failed_cnt,  0) AS TotalPayoutFailed'),
            DB::raw('COALESCE(pout.payout_failed_sum,  0) AS SumPayoutFailed'),
        ]);
}
}
