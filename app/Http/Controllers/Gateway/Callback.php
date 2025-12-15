<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment_request;
use App\Models\Wallet;
use App\Http\Controllers\Logics_building;
use Illuminate\Support\Facades\Log;

class Callback extends Controller
{
    protected $LogicsApi;

    public function __construct(Logics_building $LogicsApi)
    {
        $this->LogicsApi = $LogicsApi;
    }
    
    public function usdpay_callback(Request $r){
        if(!isset($r->status) || $r->status != "success" || !isset($r->mchOrderNo)){
            return response()->json(["status"=>false,"message"=>"Server access denied"]);
        }
        
        $OrderId = $r->mchOrderNo;
        $trn_id = rand(111111111111,999999999999);
        $payment_trn = Payment_request::where('transaction_id',$OrderId)->where('status',0)->first();
        if($payment_trn){
            $iNITAEAmount = floatval($payment_trn->amount);
            $payment_trn->data1 = $trn_id;
            $payment_trn->data2 = $trn_id;
            $payment_trn->status = 1;
            if ($payment_trn->save()) {
                $amount = $iNITAEAmount;
                if($payment_trn->data3 == 1){
                    $finalamount = $amount-$payment_trn->tax;
                    addtransaction($payment_trn->userid, 'payin', 'credit', $finalamount, '', 1, $trn_id);
                    $this->LogicsApi->AddFundToPayin($payment_trn->userid,$finalamount);
                    $callbackdata = array("status"=>"success","client_txn_id"=>$payment_trn->transaction_id,"amount"=>$payment_trn->amount,"utr"=>$payment_trn->data2);
                    $callback = user('callback',$payment_trn->userid);
                    $this->LogicsApi->CallbacksendToClient($callback,json_encode($callbackdata));
                }else{
                    addtransaction($payment_trn->userid, 'add_fund', 'credit', $iNITAEAmount, '', 1, $trn_id);
                    $this->LogicsApi->AddFundToWallet($payment_trn->userid,$iNITAEAmount);
                }
                $response = array('status' => true, 'title' => "Payment Successfully Added!!");
            }else{
                $response = array('status' => false, 'title' => "Error in data add in order!!");
            }
        }else{
            $response = array('status' => false, 'title' => "Order not Found!!");
        }
        return response()->json($response);
    }
}
