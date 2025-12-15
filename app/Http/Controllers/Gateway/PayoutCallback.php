<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use App\Models\Logs;
use App\Http\Controllers\Logics_building;
use Illuminate\Support\Facades\Log;
use DB;

class PayoutCallback extends Controller
{
    protected $LogicsApi;

    public function __construct(Logics_building $LogicsApi)
    {
        $this->LogicsApi = $LogicsApi;
    }
    
    public function universepay(Request $r){
        // $log = new Logs;
        // $log->uniqueid = "universepay";
        // $log->value = json_encode($r->all());
        // $log->save();
        // Log::info('universepay: ', $r->all());
        $OrderId = $r->orderId;
        $UTR = $r->utr;
        $StatusCode = $r->status;
        $Status = $r->status;
        $message = $r->message;
        if($StatusCode == "Completed"){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.payout_id',$OrderId)->first();
            if(!$data){
                return "Data not found";
            }
            $OrderId = $data->transaction_id;
            if($Status == "Completed"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
            }else{
                $callbackdata = array("transaction_id"=>$OrderId,"status"=>"failed","utr"=>$UTR);
                $Final_amount = $data->amount;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
            }
            if($data->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
            }
            return $callbackdata;
        }
        return "False";
    }
    public function CashKavchPayout(Request $r){
        $log = new Logs;
        $log->uniqueid = "CashKavach";
        $log->value = json_encode($r->all());
        $log->save();
        Log::info('CallbackWaoPay: ', $r->all());
        // $OrderId = $r->orderId;
        // $UTR = $r->utr;
        // $StatusCode = $r->status;
        // $Status = $r->status;
        // $message = $r->message;
        // if($StatusCode == "Completed"){
        //     $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.payout_id',$OrderId)->first();
        //     if(!$data){
        //         return "Data not found";
        //     }
        //     $OrderId = $data->transaction_id;
        //     if($Status == "Completed"){
        //         PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
        //         $callbackdata = array("transaction_id"=>$OrderId,"status"=>"success","utr"=>$UTR);
        //         $flagforCallback = 1;
        //     }else{
        //         $callbackdata = array("transaction_id"=>$OrderId,"status"=>"failed","utr"=>$UTR);
        //         $Final_amount = $data->amount;
        //         Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
        //         PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
        //         $flagforCallback = 1;
        //     }
        //     if($data->out_callback != "" && $flagforCallback == 1){
        //         $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
        //     }
        //     return $callbackdata;
        // }
        return "False";
    }
    public function decrypt($crypt, $key, $type = 'AES-256-CBC', $iv = '0123456789abcdef')
{
    $crypt = base64_decode($crypt);

    $padtext = openssl_decrypt(
        $crypt,
        $type,
        base64_decode($key),
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
        $iv
    );

    if ($padtext === false) {
        return false;
    }

    $pad = ord($padtext[strlen($padtext) - 1]);

    if ($pad > strlen($padtext)) {
        return false;
    }

    // Validate padding:
    if (strspn($padtext, $padtext[strlen($padtext) - 1], strlen($padtext) - $pad) != $pad) {
        return "Error";
    }

    // Remove padding:
    return substr($padtext, 0, -$pad);
}
    public function safexpaypayout(Request $r){
        $log = new Logs;
        $log->uniqueid = "Safexpaypayout";
        $log->value = json_encode($r->all());
        $log->save();
        $payload = $r->payload;
        if(!isset($payload)){
            return false;
        }
        $data = $this->decrypt($payload, "SSCmJfP50k5fbBi+aq8/acpYpTmfbwppwA+udcxSgtY=", 'AES-256-CBC');
        if(!$data || $data == ""){
            return false;
        }
        $r = json_decode($data);
        if(!isset($r->transactionDetails)){
            return false;
        }
        
        $TXNData = $r->transactionDetails;
        
        // return $r;
        $OrderId = $TXNData->orderRefNo;
        $UTR = $TXNData->bankRefNo;
        $StatusCode = $TXNData->txnStatus;
        $Status = $TXNData->txnStatus;
        $message = "";
        //FAILED,SUCCESS
        // if($StatusCode == "SUCCESS"){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.txnid2',$OrderId)->first();
            if(!$data){
                return "Data not found";
            }
            $OrderId = $data->transaction_id;
            if($Status == "SUCCESS"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
                echo "success";
            }else{
                $callbackdata = array("transaction_id"=>$OrderId,"status"=>"failed","utr"=>$UTR);
                $Final_amount = $data->amount;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
            }
            if($data->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
            }
            return $callbackdata;
        // }
        return "False";
    }
    public function usdpay(Request $r){
        $log = new Logs;
        $log->uniqueid = "USDPAY";
        $log->value = json_encode($r->all());
        $log->save();
        // Log::info('CallbackWaoPay: ', $r->all());
        $OrderId = $r->txn_id;
        $UTR = "00";
        $StatusCode = $r->status;
        $Status = $r->status;
        $message = "";
        if($StatusCode == "Success"){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id',$OrderId)->first();
            if(!$data){
                return "Data not found";
            }
            $OrderId = $data->transaction_id;
            if($Status == "Completed"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
            }else{
                $callbackdata = array("transaction_id"=>$OrderId,"status"=>"failed","utr"=>$UTR);
                $Final_amount = $data->amount;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
            }
            if($data->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
            }
            return $callbackdata;
        }
        return "False";
    }
    public function UniqPayCallback(Request $r){
        $log = new Logs;
        $log->uniqueid = "UNIQPAY";
        $log->value = json_encode($r->all());
        $log->save();
        // Log::info('CallbackWaoPay: ', $r->all());
        $OrderId = $r->custUniqRef;
        $UTR = $r->utr;
        $StatusCode = $r->statusCode;
        $Status = $r->statusCode;
        $message = "";
        if($OrderId !=""){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id',$OrderId)->first();
            if(!$data){
                return "Data not found";
            }
            $am = $data->amount;
            $tax = $data->tax;
            $OrderId = $data->transaction_id;
            if($Status == "TXNS"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
            }else if($Status == "TXNF"){
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"failed","utr"=>$UTR);
                $Final_amount = $data->amount+$data->tax;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
            }
            if($data->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
            }
            return $callbackdata;
        }
        return "False";
    }
    public function UniqPayCallbackReconCilation(){
        // $log = new Logs;
        // $log->uniqueid = "UNIQPAY";
        // $log->value = json_encode($r->all());
        // $log->save();
        // Log::info('CallbackWaoPay: ', $r->all());
        $exist = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->orderBy('id','ASC')->get();
        // $exist = PayoutRequest::where('status',0)->limit(10)->get();
        // return count($exist);
        foreach($exist as $rr){
        $r = $this->LogicsApi->UNIQPAYPayoutStatusApi($rr->transaction_id);
        // return $r;
        $flagforCallback = 0;
        if(isset($r->status) && $r->status == "NOT_FOUND"){
            $callbackdata = array("transaction_id"=>$rr->transaction_id,"amount"=>$rr->amount,"fees"=>$rr->tax,"status"=>"failed","utr"=>"00");
                $Final_amount = $rr->amount+$rr->tax;
                Wallet::where('userid',$rr->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$rr->transaction_id)->update(["status"=>2,"utr"=>"00","remark"=>"Not found in Bank"]);
                $flagforCallback = 1;
                echo "Failed <br>";
        }elseif(isset($r->status) && $r->status == "FAILED"){
            $callbackdata = array("transaction_id"=>$rr->transaction_id,"amount"=>$rr->amount,"fees"=>$rr->tax,"status"=>"failed","utr"=>"00");
                $Final_amount = $rr->amount+$rr->tax;
                Wallet::where('userid',$rr->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                $message = $r->message;
                PayoutRequest::where('transaction_id',$rr->transaction_id)->update(["status"=>2,"utr"=>"00","remark"=>$message]);
                $flagforCallback = 1;
                echo "Failed <br>";
        }else{
        $OrderId = $r->response->transactionDetails->transferId;
        $UTR = $r->response->data->utr ?? "00";
        $StatusCode = $r->response->statusCode;
        $Status = $r->response->statusCode;
        $message = "";
        if($OrderId !=""){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id',$OrderId)->first();
            if(!$data){
                echo "Data not found <br>";
            }
            $am = $rr->amount;
            $tax = $rr->tax;
            $OrderId = $rr->transaction_id;
            if($Status == "TXNS"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
                echo "Success $OrderId <br>";
            }else if($Status == "TXNF"){
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"failed","utr"=>$UTR);
                $Final_amount = $data->amount+$data->tax;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
                echo "Failed By Api <br>";
            }
            if($rr->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($rr->out_callback,json_encode($callbackdata));
            }
            echo "Final <br>";
        }
        }
        }
        return "False";
    }
    public function VeloZPayCallback(Request $r){
        // $log = new Logs;
        // $log->uniqueid = "VeloZPAY";
        // $log->value = json_encode($r->all());
        // $log->save();
        
        
        $event = $r->event_type;
        // return $r->data['object']['id'];
        $OrderId = $r->data['object']['merchant_order_id'];
        $UTR =  $r->data['object']['bank_reference_id'];
        $StatusCode = $r->data['object']['status'];
        $Status = $r->data['object']['status'];
        $message = "";
        if($event == "payout.txn_succeeded" && $OrderId !=""){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id',$OrderId)->first();
            if(!$data){
                return "Data not found";
            }
            $am = $data->amount;
            $tax = $data->tax;
            $OrderId = $data->transaction_id;
            if($event == "payout.txn_succeeded"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
            }else if($event == "payout.txn_failed"){
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"failed","utr"=>"00");
                $Final_amount = $data->amount+$data->tax;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
            }
            if($data->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
            }
            return $callbackdata;
        }
        return "False";
    }
    
    public function MotherPayPayoutCallback(Request $r){
        $log = new Logs;
        $log->uniqueid = "RudraxPayPayout";
        $log->value = json_encode($r->all());
        $log->save();
        
        
        $event = $r->status;
        $UTR =  $r->utr;
        $StatusCode = $r->status;
        $Status = $r->status;
        $OrderId = $r->transaction_id;
        // return $OrderId;
        $message = "";
        if($event == "success"){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id',$OrderId)->first();
            if(!$data){
                return "Data not found";
            }
            $am = $data->amount;
            $tax = $data->tax;
            $OrderId = $data->transaction_id;
            $message = $r->remark;
            if($event == "success"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
            }else if($event == "failed"){
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"failed","utr"=>"00");
                $Final_amount = $data->amount+$data->tax;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
            }
            if($data->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
            }
            return $callbackdata;
        }
        return "False";
    }
    public function finuniq_callback(Request $r){
        // DEPRECATED: This is kept for backward compatibility
        // New transactions should use: /api/payout/finqunes/callback
        // This routes to: App\Http\Controllers\Gateway\FinQunes::payoutCallback()
        $log = new Logs;
        $log->uniqueid = "FinuniquePayout";
        $log->value = json_encode($r->all());
        $log->save();
        
        
        $event = $r->status;
        $UTR =  $r->utr;
        $StatusCode = $r->status;
        $Status = $r->status;
        $OrderId = $r->reference;
        // return $OrderId;
        $message = "";
        if($OrderId !=""){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id',$OrderId)->first();
            if(!$data){
                return "Data not found";
            }
            $am = $data->amount;
            $tax = $data->tax;
            $OrderId = $data->transaction_id;
            $message = $r->remark;
            if($event == "Success"){
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
            }else if($event == "FAILED"){
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"failed","utr"=>"00");
                $Final_amount = $data->amount+$data->tax;
                Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                $flagforCallback = 1;
            }
            if($data->out_callback != "" && $flagforCallback == 1){
                $this->LogicsApi->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
            }
            return $callbackdata;
        }
        return "False";
    }
}
