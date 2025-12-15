<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PayoutRequest;
use App\Http\Controllers\Logics_building;
use App\Models\Wallet;
use DB;

class PayinFunction extends Controller
{
    
    // protected $LogicsApi;

    // public function __construct(Logics_building $LogicsApi)
    // {
    //     $this->LogicsApi = $LogicsApi;
    // }

    private function MakecURLrEQUESTpOST($URL,$DATA,$Header){
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $DATA,
          CURLOPT_HTTPHEADER => $Header,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    public function CashKavachPayoutIMPS($am,$trn_id,$mobile,$name,$number,$ifsc){
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.cashkavach.com/api/PayoutTransfer',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
              "OrderId": "'.$trn_id.'",
              "BankName": "UNION BANK OF INDIA",
              "AccountNo": "'.$number.'",
              "Ifsc": "'.$ifsc.'",
              "AccountHolderName": "'.$name.'",
              "AccountType": "Saving",
              "Amount": "'.$am.'",
              "TxnMode": "IMPS",
              "Remarks": "Refund",
              "latitude": "28.6798",
              "longitude": "77.0927"
          }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'AuthorizedKey: 5gUgBiksY1MJyQYvfCrDXp3xKaX4984tBTMirzux3dY=',
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    public function UniversePayPayoutIMPS($am,$trn_id,$mobile,$name,$number,$ifsc){
        $AuthData = $this->MakecURLrEQUESTpOST(
            "https://universepay.in/api/auth/login",
            json_encode(array('email'=>"help.a2zservices2023@gmail.com",'password'=>"Rahul@8750")),
            array('Content-Type: application/json'));
        
        if(!$AuthData->status){
            return false;
        }
        // return $AuthData;
        $token = $AuthData->access_token;
        $data = array (
        "amount"=> $am,
        "ifsc"=> $ifsc,
        "accountno"=> $number,
        "name"=> $name,
        "branch"=> "DELHI",
        "paymode"=> "IMPS",
        "remarks"=> "Refund",
        "mode"=> "bank"
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://universepay.in/api/transfer',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($data),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$token
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    public function encrypt($text, $key, $type, $iv = "0123456789abcdef", $size = 16)
{
    $pad = $size - (strlen($text) % $size);
    $padtext = $text . str_repeat(chr($pad), $pad);
    $crypt = openssl_encrypt($padtext, "AES-256-CBC", base64_decode($key), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
    return base64_encode($crypt);
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

    public function SafexpayPayoutIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
    // Prepare original payload as per your example:
    $payOutBean = [
        "mobileNo" => $mobile,
        "txnAmount" => $am,
        "accountNo" => $number,
        "ifscCode" => $ifsc,
        "bankName" => "Adarsh Limited Bank",
        "accountHolderName" => $name,
        "txnType" => "IMPS",
        "accountType" => "Saving",
        "emailId" => "test@gmail.com",
        "orderRefNo" => $trn_id,
        "count" => 0
    ];

    $payloadData = [
        "header" => [
            "operatingSystem" => "WEB",
            "sessionId" => "AGEN3920029760",  // same as uId
            "version" => "1.0.0"
        ],
        "userInfo" => new \stdClass(),
        "transaction" => [
            "requestType" => "WTW",
            "requestSubType" => "PWTB",
            "tranCode" => 0,
            "txnAmt" => 0.0,
            "surChargeAmount" => 0.0,
            "txnCode" => 0,
            "userType" => 0
        ],
        "payOutBean" => $payOutBean
    ];

    // Convert payload to JSON:
    $jsonPayload = json_encode($payloadData, JSON_UNESCAPED_SLASHES);

    // Encrypt the payload:
    $merchantKey = 'SSCmJfP50k5fbBi+aq8/acpYpTmfbwppwA+udcxSgtY='; // Given
    $encryptedPayload = $this->encrypt($jsonPayload, $merchantKey, 'AES-256-CBC');
    

    // Prepare final request:
    $finalRequest = [
        "payload" => $encryptedPayload,
        "uId" => "AGEN3920029760"
    ];

    // Send CURL request:
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://remittance.touras.in/agWalletAPI/v2/agg',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($finalRequest),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'operatingSystem: WEB'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response);
    // return $response;
    if(isset($response->payload)){
        return $this->decrypt($response->payload, $merchantKey, 'AES-256-CBC');
    }else{
        return $response;
    }
    echo $response;
    return json_decode($response);
}
    public function SafexpayPayoutReconcile()
{
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    $exist = PayoutRequest::where('payout_requests.status', 0)
        ->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')
        ->select('payout_requests.*', 'users.out_callback')
        ->orderBy('id', 'DESC')
        ->get();

    $merchantKey = 'SSCmJfP50k5fbBi+aq8/acpYpTmfbwppwA+udcxSgtY=';
    $uId = "AGEN3920029760";

    foreach ($exist as $rr) {
        $OrderId = $rr->transaction_id;
        $orderiddd = $rr->txnid2 == "" ? $rr->transaction_id : $rr->txnid2;

        $payOutBean = [
            "payoutId" => "",
            "orderRefNo" => $orderiddd
        ];

        $payloadData = [
            "header" => [
                "operatingSystem" => "WEB",
                "sessionId" => $uId,
                "version" => "1.0.0"
            ],
            "userInfo" => new \stdClass(),
            "transaction" => [
                "requestType" => "TMH",
                "requestSubType" => "STCHK",
                "tranCode" => 0,
                "txnAmt" => 0.0
            ],
            "payOutBean" => $payOutBean
        ];

        $jsonPayload = json_encode($payloadData, JSON_UNESCAPED_SLASHES);
        $encryptedPayload = $this->encrypt($jsonPayload, $merchantKey, 'AES-256-CBC');

        $finalRequest = [
            "payload" => $encryptedPayload,
            "uId" => $uId
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://remittance.touras.in/agWalletAPI/v2/agg',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($finalRequest),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'operatingSystem: WEB'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        if (isset($response->payload)) {
            $datata = json_decode($this->decrypt($response->payload, $merchantKey, 'AES-256-CBC'));

            if (isset($datata->response->code) && $datata->response->code == "0000" && isset($datata->payOutBean->bankStatus)) {
                $bankStatus = $datata->payOutBean->bankStatus;
                $UTR = $datata->payOutBean->bankRefNo ?? '00';
                $am = $rr->amount;
                $tax = $rr->tax;
                $message = "Reconciled status update";
                $flagforCallback = 0;
                $callbackdata = [];

                if ($bankStatus == "FAILED") {
                    $Final_amount = $am + $tax;

                    Wallet::where('userid', $rr->userid)->update([
                        "payout" => DB::raw("payout + $Final_amount")
                    ]);

                    PayoutRequest::where('transaction_id', $OrderId)->update([
                        "status" => 2,
                        "utr" => "00",
                        "remark" => "Auto-refund due to failure"
                    ]);

                    $callbackdata = [
                        "transaction_id" => $OrderId,
                        "amount" => $am,
                        "fees" => $tax,
                        "status" => "refund",
                        "utr" => "00"
                    ];

                    $flagforCallback = 1;
                    echo "Failed $OrderId<br>";
                } elseif ($bankStatus == "SUCCESS") {
                    // return $datata;
                    PayoutRequest::where('transaction_id', $OrderId)->update([
                        "status" => 1,
                        "utr" => $UTR,
                        "remark" => "Reconciled success"
                    ]);

                    $callbackdata = [
                        "transaction_id" => $OrderId,
                        "amount" => $am,
                        "fees" => $tax,
                        "status" => "success",
                        "utr" => $UTR
                    ];

                    $flagforCallback = 1;
                    echo "Success $OrderId<br>";
                }

                // if ($rr->out_callback != "" && $flagforCallback == 1) {
                //     CallbacksendToClientAdarsh($rr->out_callback, json_encode($callbackdata));
                // }
            }
        } else {
            echo "Error response for $OrderId<br>";
        }
    }
}


}
