<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\club_user;
use App\Models\Contact;
use App\Models\Donations;
use App\Models\Investment;
use App\Models\Order;
use App\Models\Packages;
use App\Models\Payment_request;
use App\Models\Products;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\PayoutRequest;
use App\Models\Transaction_password;
use App\Http\Controllers\Gateway\PayinFunction;
use App\Models\Logs;
use App\Models\user;
use App\Models\User_Bank;
use App\Models\User_Cart;
use App\Models\User_Wishlist;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
// ✅ ONLY EASEBUZZ IS ACTIVE - Other gateways commented out
// use App\Http\Controllers\Gateway\Razorpay;
// use App\Http\Controllers\Gateway\QutePaisa;
// use App\Http\Controllers\Gateway\Paydeer;
// use App\Http\Controllers\Gateway\PayPayout;
// use App\Http\Controllers\Gateway\PayU;
use Hash;
use Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * ⚠️ IMPORTANT: ONLY EASEBUZZ (Gateway 28) IS ACTIVE
 * 
 * All other gateway methods in this file are kept for reference only.
 * They will NOT be called because Helper.php restricts to Easebuzz only.
 * 
 * Active Gateway: Easebuzz (ID: 28)
 * Credentials: app/Http/Controllers/Gateway/Easebuzz.php
 */

class Logics_building extends Controller
{
    protected $PayinFunction;
    // protected $sabpaisa;

    public function __construct(PayinFunction $PayinFunction)
    {
        $this->PayinFunction = $PayinFunction;
        // $this->Sabpaisa = $sabpaisa;
    }
    public $PayoutClose = false;

    public function contact_us(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops!! Invalid Action!!");
        $r->validate([
            "name" => "required",
            "email" => "required",
            "mobile" => "required",
            "message" => "required",
        ]);

        $c = new Contact;
        $c->name = $r->name;
        $c->email = $r->email;
        $c->mobile = $r->mobile;
        $c->message = $r->message;
        if ($c->save()) {
            $response = array('status' => 1, 'title' => "Your Request is sended successfully");
        } else {
            $response = array('status' => 0, 'title' => "Oops! Something wents wrong, Please try again.");
        }
        return response()->json($response);
    }

    public function AuthAaadharApi()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.sandbox.co.in/authenticate",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Accept: */*",
                "x-api-key: key_live_hePphhh1YXsQjvvBitlzhBbKpOw7qQsA",
                "x-api-secret: secret_live_IqIhtGpfBVp3vjVJZXS7tdVGFrRbVI7r",
                "x-api-version: 1.0"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response)->access_token;
        }
    }
    //User Logic
    public function aadhar_verify(Request $r)
    {
        $authorisation = $this->AuthAaadharApi();
        if (!$r->aadhaar_number || $r->aadhaar_number == "") {
            return response()->json(array("status" => false, "message" => "Aaadhar Card Number Required!!"));
        }
        if (!$r->userid || $r->userid == "") {
            return response()->json(array("status" => false, "message" => "Userid Required!!"));
        }
        $charges = setting("charge");
        $user = user::where("users.userid", $r->userid)->leftJoin('wallets', 'users.userid', '=', 'wallets.userid')->first();
        if ($user) {
            if ($user->wallet < $charges) {
                return response()->json(array("status" => false, "message" => "Insufficient balance"));
            }
        } else {
            return response()->json(array("status" => false, "message" => "User Not Found"));
        }
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.sandbox.co.in/kyc/aadhaar/okyc/otp",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n  \"aadhaar_number\": \"$r->aadhaar_number\"\n}",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Authorization: " . $authorisation,
                "Content-Type: application/json",
                "x-api-key: key_live_hePphhh1YXsQjvvBitlzhBbKpOw7qQsA",
                "x-api-version: 1.0"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if (json_decode($response)->code == 200) {
            $trnid = addtransaction($user->userid, 'aadhar_uses', 'debit', $charges, $r->aadhaar_number, 0, $response);
        }
        return response()->json(array('status' => true, 'trn' => $trnid, 'auth' => $authorisation, 'data' => json_decode($response)));
    }
    public function aadhar_otp_verify(Request $r)
    {
        $authorisation = $this->AuthAaadharApi();
        $r->validate([
            "otp" => "required",
            "ref_id" => "required",
            "auth" => "required"
        ]);
        $trnn = Transaction::where("id", $r->ref_id)->first();
        if (!$trnn) {
            return response()->json(array("status" => false, "message" => "Transaction Not Found"));
        }
        $ref_id = json_decode($trnn->data2)->data->ref_id;
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.sandbox.co.in/kyc/aadhaar/okyc/otp/verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n  \"otp\": \"$r->otp\",\n  \"ref_id\": \"$ref_id\"\n}",
            CURLOPT_HTTPHEADER => [
                "Accept: */*",
                "Authorization: " . $r->auth,
                "Content-Type: application/json",
                "x-api-key: key_live_hePphhh1YXsQjvvBitlzhBbKpOw7qQsA",
                "x-api-version: 1.0"
            ],
        ]);
        addwallet($trnn->userid, $trnn->amount, '-', 'wallet');
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        Transaction::where("id", $r->ref_id)->update(["data3" => $response, "status" => 1]);
        return response()->json(json_decode($response));
    }
    public function edit_profile(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops!! Invalid Action!!", 'message' => "");
        user::where('userid', user('userid'))->update([
            "address" => $r->address_1,
            "address_1" => $r->address_2,
            "dob" => $r->dob,
            // "father_name" => $r->father_name,
            // "gender" => $r->gender,
            "mobile_2" => $r->mobile_2,
            "state" => $r->state,
            "pincode" => $r->pincode,
            "city" => $r->city,
            // "nominee_relation" => $r->nominee_relation,
            // "nominee_name" => $r->nominee_name,
            "aadhar_card" => $r->aadhar_no,
            "pan_card" => $r->pan_no,
            "company_name" => $r->company_name ?? null,
        ]);
        if (isset($r->bank_name) || isset($r->account_no) || isset($r->ifsc_code) || isset($r->upiid)) {
            $exist = User_Bank::where('userid', user('userid'))->first();
            if ($exist) {
                User_Bank::where('userid', user('userid'))->update([
                    "bank_name" => $r->bank_name,
                    "account_no" => $r->account_no,
                    "ifsc_code" => $r->ifsc_code,
                    // "upiid" => $r->upiid,
                ]);
            } else {
                $bank = new User_Bank;
                $bank->userid = user('userid');
                if (isset($r->bank_name) && $r->bank_name != '') {
                    $bank->bank_name = $r->bank_name;
                }
                if (isset($r->account_no) && $r->account_no != '') {
                    $bank->account_no = $r->account_no;
                }
                if (isset($r->ifsc_code) && $r->ifsc_code != '') {
                    $bank->ifsc_code = $r->ifsc_code;
                }
                if (isset($r->upiid) && $r->upiid != '') {
                    $bank->upiid = $r->upiid;
                }
                $bank->save();
            }
        }
        $response = array('status' => 1, 'title' => "Update Successfully");
        return response()->json($response);
    }
     public function rudraxpaypayoutreconcile(){
        // $log = new Logs;
        // $log->uniqueid = "UNIQPAY";
        // $log->value = json_encode($r->all());
        // $log->save();
        // Log::info('CallbackWaoPay: ', $r->all());
        $exist = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->orderBy('id','DESC')->get();
        // $exist = PayoutRequest::where('status',0)->limit(10)->get();
        // return count($exist);
        foreach($exist as $rr){
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://merchant.rudraxpay.com/api/payout/checkstatus',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "userid": "RXP10100",
            "orderid": "'.$rr->transaction_id.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $r = json_decode($response);
        // return $rr;
        $flagforCallback = 0;
        if(isset($r->status) && $r->status == "failed"){
            $callbackdata = array("transaction_id"=>$rr->transaction_id,"amount"=>$rr->amount,"fees"=>$rr->tax,"status"=>"failed","utr"=>"00");
                $Final_amount = $rr->amount+$rr->tax;
                Wallet::where('userid',$rr->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                $message = $r->message;
                PayoutRequest::where('transaction_id',$rr->transaction_id)->update(["status"=>2,"utr"=>"00","remark"=>$message]);
                $flagforCallback = 1;
                echo $rr->transaction_id." Failed <br>";
        }elseif(isset($r->status) && $r->status == "success"){
        $OrderId = $rr->transaction_id;
        $UTR = $r->utr ?? "00";
        $StatusCode = $r->status;
        $Status = $r->status;
        $message = "";
        if($OrderId !=""){
            $data = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id',$OrderId)->first();
            if(!$data){
                echo "Data not found <br>";
            }
            $am = $rr->amount;
            $tax = $rr->tax;
            $OrderId = $rr->transaction_id;
                PayoutRequest::where('transaction_id',$OrderId)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                $callbackdata = array("transaction_id"=>$OrderId,"amount"=>$am,"fees"=>$tax,"status"=>"success","utr"=>$UTR);
                $flagforCallback = 1;
                echo $rr->transaction_id." Success $OrderId <br>";
        }
        }
            if($rr->out_callback != "" && $flagforCallback == 1){
                $this->CallbacksendToClient($rr->out_callback,json_encode($callbackdata));
            }
            echo "Final <br>";
        
        }
        return "False";
    }
    public function reset_password(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops!! Invalid Action!!", 'message' => "");
        $r->validate([
            "password" => "required",
            "repassword" => "required",
        ]);
        if ($r->password == $r->repassword) {
            user::where('userid', user('userid'))->update([
                "password" => Hash::make($r->password),
            ]);
            $response = array('status' => 1, 'title' => "Passowrd reset successfully");
        } else {
            $response = array('status' => 0, 'title' => "Passowrd not match");
        }
        return response()->json($response);
    }
    public function admin_reset_password(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops!! Invalid Action!!", 'message' => "");
        $r->validate([
            "userid" => "required",
            "password" => "required",
            "repassword" => "required",
        ]);
        if ($r->password == $r->repassword) {
            user::where('userid', $r->userid)->update([
                "password" => Hash::make($r->password),
            ]);
            $response = array('status' => 1, 'title' => "Passowrd reset successfully");
        } else {
            $response = array('status' => 0, 'title' => "Passowrd not match");
        }
        return response()->json($response);
    }
    public function send_otp_for_user(Request $r)
    {
        $r->validate([
            'userid' => 'required',
        ]);
        $existuser = user::where('userid', $r->userid)->first();
        if ($existuser) {
            $newotp = rand(0, 9) . '' . rand(0, 9) . '' . rand(0, 9) . '' . rand(0, 9) . '' . rand(0, 9) . '' . rand(0, 9);
            $r->session()->put('otp', $newotp);
            $response = array('status' => 1, 'title' => $newotp . " |Success!! OTP Send successfully!!", 'message' => "");
        } else {
            $response = array('status' => 0, 'title' => "Oops!! User not found!!", 'message' => "");
        }
        return response()->json($response);
    }
    public function transaction_password(Request $r)
    {
        $response = array('status' => 0, 'title' => "Oops!! Invalid Action!!", 'message' => "");
        $r->validate([
            "otp" => "required",
            "newpassword" => "required",
            "repassword" => "required",
        ]);
        if (session()->has('otp')) {
            if ($r->otp == session()->get('otp')) {
                if ($r->newpassword == $r->repassword) {
                    $existpassword = Transaction_password::where('userid', user('userid'))->first();
                    if ($existpassword) {
                        Transaction_password::where('userid', user('userid'))->update([
                            "password" => Hash::make($r->newpassword),
                        ]);
                        session()->forget('otp');
                        $response = array('status' => 1, 'title' => "Transaction Password Updated successfully");
                    } else {
                        $transp = new Transaction_password;
                        $transp->userid = user('userid');
                        $transp->password = Hash::make($r->newpassword);
                        if ($transp->save()) {
                            session()->forgot('otp');
                            $response = array('status' => 1, 'title' => "Transaction Password Generate successfully");
                        }
                    }
                } else {
                    $response = array('status' => 0, 'title' => "Transaction Passowrd not match");
                }
            } else {
                $response = array('status' => 0, 'title' => "Incorrect OTP!!");
            }
        } else {
            $response = array('status' => 0, 'title' => "OTP Timeout!!");
        }
        return response()->json($response);
    }
    //Admin Logic
    public function add_category(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'name' => 'required',
            'image' => 'required',
        ]);
        $category = new Category;
        $category->name = $r->name;
        $category->image = imageupload($r->file('image'), time(), 'category/' . time() . '/')['filePath'];
        if ($category->save()) {
            $response = array('status' => 1, 'title' => "Category Added Successfully!!");
        }
        return response()->json($response);
    }
    public function update_category(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        if (isset($r->image) && $r->image != '' && $r->image != null) {
            $updatequery = [
                'name' => $r->name,
                'image' => imageupload($r->file('image'), time(), 'category/' . time() . '/')['filePath'],
            ];
        } else {
            $updatequery = [
                'name' => $r->name,
            ];
        }
        $category = Category::where('id', $r->id)->update($updatequery);
        $response = array('status' => 1, 'title' => "Category Updated Successfully!!");
        return response()->json($response);
    }
    public function delete_category(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = Category::where('id', $r->id)->delete();
        $response = array('status' => 1, 'title' => "Category Deleted Successfully!!");
        return response()->json($response);
    }
    public function update_setting(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = Setting::where('id', $r->id)->update([
            'value' => $r->value,
        ]);
        $response = array('status' => 1, 'title' => "Setting Updated Successfully!!");
        return response()->json($response);
    }
    public function becomefranchise($id)
    {
        $exist = user::where('userid', $id)->first();
        if ($exist) {
            user::where('userid', $id)->update(["role" => 'franchise', 'isadmin' => 1]);
            $response = array('status' => 1, 'title' => "Updated Successfully!!");
        } else {
            $response = array('status' => 0, 'title' => "User Not Found!!");
        }
        return response()->json($response);
    }
    public function blockuserbyadmin($id)
    {
        $exist = user::where('userid', $id)->first();
        if ($exist) {
            if ($exist->status == 1) {
                user::where('userid', $id)->update(["status" => 0]);
            } else {
                user::where('userid', $id)->update(["status" => 1]);
            }
            $response = array('status' => 1, 'title' => "Updated Successfully!!");
        } else {
            $response = array('status' => 0, 'title' => "User Not Found!!");
        }
        return response()->json($response);
    }
    public function delete_setting(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = Setting::where('id', $r->id)->delete();
        $response = array('status' => 1, 'title' => "Setting Deleted Successfully!!");
        return response()->json($response);
    }
    public function delete_club_user(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = club_user::where('id', $r->id)->delete();
        $response = array('status' => 1, 'title' => "Club User Deleted Successfully!!");
        return response()->json($response);
    }
    public function delete_user(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = user::where('userid', $r->id)->delete();
        $wallet = Wallet::where('userid', $r->id)->delete();
        $response = array('status' => 1, 'title' => "User Deleted Successfully!!");
        return response()->json($response);
    }
    public function view_product(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = Products::where('id', $r->id)->first();
        if ($category) {
            $ex = setting("exchange_rate");
            $doll_am = $category->amount / $ex;
            $am = balance($category->amount, '₹');
            $pam = balance(percentage($doll_am, 30));
            $dd = array('amount' => $am, 'pamount' => $pam);
            $response = array('status' => 1, 'title' => "Product Found", 'body' => $dd);
        } else {
            $response = array('status' => 0, 'title' => "Product Not Found");
        }
        return response()->json($response);
    }
    public function sell_now_product(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'product' => 'required',
        ]);
        $product = Products::where('id', $r->product)->first();
        if ($product) {
            $user = user::where('userid', $r->userid)->first();
            if ($user) {
                $ex = setting("exchange_rate");
                $doll_am = floatval($product->amount / $ex);
                $pam = percentage($doll_am, 30);
                $loginuser = admin("userid");
                if (wallet($loginuser, 'string', 'wallet') >= $pam) {
                    //Admin deduction
                    addwallet($loginuser, $pam, '-', 'wallet');
                    addtransaction($loginuser, 'sell', 'debit', $pam, $user->userid, 1, $r->product);

                    //User addition
                    $namount = floor($doll_am / 100) * 100;
                    addtransaction($user->userid, 'activation', 'debit', $namount, $loginuser, 1, 3);
                    $alldirect = user::where('sponserid', $user->userid)->where(function ($query) {
                        $query->where('package', 1)->orWhere('product', 1);
                    })->count();
                    $ppp1 = $alldirect > 0 ? 2 : 1;
                    $invest = new Investment;
                    $invest->userid = $user->userid;
                    $invest->amount = $namount;
                    $invest->type = "product";
                    $invest->profit = $namount * $ppp1;
                    if ($invest->save()) {
                        user::where('userid', $r->userid)->update([
                            "activated_at" => date('Y-m-d h:i:s'),
                            "product" => 1,
                        ]);
                        $response = array('status' => 1, 'title' => "Product sell Successfully");
                    }
                } else {
                    $response = array('status' => 0, 'title' => "Franchise not have sufficient amount, Please recharge by admin!!");
                }
            } else {
                $response = array('status' => 0, 'title' => "User Not Found!!");
            }
        } else {
            $response = array('status' => 0, 'title' => "Product Not Found!!");
        }
        return response()->json($response);
    }
    public function delete_product(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = Products::where('id', $r->id)->delete();
        $response = array('status' => 1, 'title' => "Product Deleted Successfully!!");
        return response()->json($response);
    }
    public function add_product(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'name' => 'required',
            'category' => 'required',
            'old_amount' => 'required',
            'amount' => 'required',
            'description' => 'required',
            'specifications' => 'required',
            'image1' => 'required',
        ]);
        $pid = time();
        $uid = admin('userid');
        $category = new Products;
        $category->name = $r->name;
        $category->uid = $uid;
        $category->pid = $pid;
        $category->cid = $r->category;
        $category->old_amount = $r->old_amount;
        $category->amount = $r->amount;
        $category->description = $r->description;
        $category->specification = $r->specifications;
        $category->image1 = imageupload($r->file('image1'), 'image1', 'product/' . $pid . '/')['filePath'];
        if (isset($r->image2) && $r->image2 != '') {
            $category->image2 = imageupload($r->file('image2'), 'image2', 'category/' . $pid . '/')['filePath'];
        }
        if (isset($r->image3) && $r->image3 != '') {
            $category->image3 = imageupload($r->file('image3'), 'image3', 'category/' . $pid . '/')['filePath'];
        }
        if (isset($r->image4) && $r->image4 != '') {
            $category->image4 = imageupload($r->file('image4'), 'image4', 'category/' . $pid . '/')['filePath'];
        }
        if (isset($r->image5) && $r->image5 != '') {
            $category->image5 = imageupload($r->file('image5'), 'image5', 'category/' . $pid . '/')['filePath'];
        }
        if (isset($r->image6) && $r->image6 != '') {
            $category->image6 = imageupload($r->file('image6'), 'image6', 'category/' . $pid . '/')['filePath'];
        }
        if ($category->save()) {
            $response = array('status' => 1, 'title' => "Product Added Successfully!!");
        }
        return response()->json($response);
    }
    public function upgradeid(Request $r)
    {
        $amount = 0;
        $r->validate([
            "userid" => "required",
            "package" => "required",
            "tpassword" => "required",
            "amount" => "required",
        ]);

        $exist = user::where('userid', $r->userid)->where('status', 1)->first();
        if ($exist) {
            if ($r->package == "2" || $r->package == 2) {
                $amount = $r->amount;
            } else {
                if ($exist->package <= $r->package) {
                    $amount = packages($r->package, 'amount');
                }
            }
            if ($amount > 0) {
                if (checktransactionpassword(user('userid'), $r->tpassword)) {
                    if (wallet(user('userid'), 'int', 'wallet') > 0 && wallet(user('userid'), 'int', 'wallet') >= $amount) {
                        addwallet(user('userid'), $amount, '-', 'wallet');
                        addtransaction(user('userid'), 'activation', 'debit', $amount, $r->userid, 1, $r->package);
                        user::where('userid', $r->userid)->update([
                            "package" => 1,
                            "activated_at" => date('Y-m-d h:i:s'),
                        ]);
                        $alldirect = user::where('sponserid', $r->userid)->where(function ($query) {
                            $query->where('package', 1)->orWhere('product', 1);
                        })->count();
                        $ppp1 = $alldirect > 0 ? 3 : 2;
                        // return $ppp1;
                        $invest = new Investment;
                        $invest->userid = $r->userid;
                        $invest->amount = $amount;
                        $invest->profit = $amount * $ppp1;
                        if ($invest->save()) {
                            if (user('userid') != $r->userid) {
                                addtransaction($r->userid, 'activation', 'debit', $amount, user('userid'), 1, $r->package);
                            }
                            $response = array('status' => 1, 'title' => "User Activated Successfully!!");
                        }
                    } else {
                        $response = array('status' => 0, 'title' => "Not sufficient Amount!!");
                    }
                } else {
                    $response = array('status' => 0, 'title' => "Please make sure, transaction password is craete.");
                }
            } else {
                $response = array('status' => 0, 'title' => "You are already activated!!");
            }

        } else {
            $response = array('status' => 0, 'title' => "UserId Not Found!!");
        }
        return response()->json($response);
    }

    public function usercheck(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
        ]);
        $data = user::where('userid', $r->userid)->where('status', '1')->where(function ($query) {
            $query->where('package', 1)->orWhere('product', 1);
        })->first();
        if ($data) {
            $category = "";
            $response = array('status' => 1, 'data' => '<span class="text-success">' . $data->name . '</span>', 'title' => "Userid Correct", "packages" => $category);
        } else {
            $response = array('status' => 0, 'data' => '<span class="text-danger">User Not Found or Not Activated yet!!</span>', 'title' => "Userid Incorrect");
        }
        return response()->json($response);
    }

    public function admin_settlement_aprove(Request $r)
    {
        $r->validate([
            "userid" => 'required',
            "amount" => 'required',
            "tax" => 'required',
            "bamount" => 'required',
            "startdate" => 'required',
            "utr" => 'required',
            "enddate" => 'required'
        ]);
        
        // Get description from request
        $description = $r->input('description', 'Settlement by Admin');
        
        $exist = user::where('userid', $r->userid)->first();
        if (!$exist) {
            return response()->json(array('status' => 0, 'title' => "Oops! User not found!!"));
        }
        
        // Get hold amount (default to 0 if not provided)
        $holdAmount = floatval($r->input('hold_amount', 0));
        
        // Calculate amounts
        // bamount = gross amount INCLUDING tax (from payment_requests.amount)
        // tax = service charge/tax amount
        // grossAmountWithoutTax = gross amount WITHOUT tax (for storage in data3)
        $grossAmountWithTax = floatval($r->bamount); // Total gross including tax
        $taxAmount = floatval($r->tax);
        $grossAmountWithoutTax = $grossAmountWithTax - $taxAmount; // Gross without tax (for data3)
        $netAmount = $grossAmountWithoutTax; // Net amount = gross without tax
        
        // Define $grossAmount for backward compatibility (same as $grossAmountWithTax)
        $grossAmount = $grossAmountWithTax;
        
        // Frontend sends settled amount (net - hold), but we need to recalculate
        $settleAmount = $netAmount - $holdAmount;
        
        // Validate hold amount doesn't exceed net amount
        if ($holdAmount < 0 || $holdAmount > $netAmount) {
            return response()->json(array('status' => 0, 'title' => "Invalid hold amount! Hold amount cannot exceed net amount."));
        }
        
        // Validate settle amount is positive
        if ($settleAmount < 0) {
            return response()->json(array('status' => 0, 'title' => "Invalid settlement! Hold amount exceeds net amount."));
        }
        
        // Start transaction for data consistency
        DB::beginTransaction();
        try {
            // Get current payin wallet balance
            $wallet = \App\Models\Wallet::where('userid', $exist->userid)->first();
            $currentPayinBalance = $wallet ? (float) $wallet->payin : 0;
            
            // Calculate the actual amount to deduct
            // If current balance is close to calculated net amount (within 1% or ₹100), deduct full balance
            // This handles rounding differences and small remaining amounts
            $difference = abs($currentPayinBalance - $netAmount);
            $tolerance = max($netAmount * 0.01, 100); // 1% or ₹100, whichever is higher
            
            if ($difference <= $tolerance && $currentPayinBalance > 0) {
                // Deduct full payin balance to avoid orphaned amounts
                $amountToDeduct = $currentPayinBalance;
            } else {
                // Use calculated amount if difference is significant
                $amountToDeduct = min($netAmount, $currentPayinBalance);
            }
            
            // Ensure we don't deduct more than available
            $amountToDeduct = max(0, min($amountToDeduct, $currentPayinBalance));
            
            // Deduct from payin wallet
            if ($amountToDeduct > 0) {
                addwallet($exist->userid, $amountToDeduct, '-', 'payin');
            }
            
            // Calculate actual settled amount (deducted amount minus hold)
            $actualSettledAmount = $amountToDeduct - $holdAmount;
            if ($actualSettledAmount < 0) {
                $actualSettledAmount = 0;
            }
            
            // Transfer hold amount to hold wallet (if any)
            if ($holdAmount > 0) {
                addwallet($exist->userid, $holdAmount, '+', 'hold');
                // Create transaction record for hold amount so it appears in add-fund-history
                // Record hold with gross (incl. tax) to keep context
                addtransaction($exist->userid, 'add_fund', 'credit', $holdAmount, 'Settlement Hold Amount', 1, $r->utr, 'Hold', $holdAmount, $grossAmountWithTax);
            }
            
            // Transfer settled amount to payout wallet
            if ($actualSettledAmount > 0) {
                addwallet($exist->userid, $actualSettledAmount, '+', 'payout');
            }
            
            // Create settlement transaction record
            // amount = settled amount (to payout), data2 = UTR, data3 = gross amount WITHOUT tax, data4 = tax, data5 = hold amount
            // data6 = startdate, data7 = enddate (to track which date range was settled)
            addtransaction($exist->userid, 'settlement', 'credit', $actualSettledAmount, $description, 1, $r->utr, $grossAmountWithoutTax, $taxAmount, $holdAmount, $r->startdate, $r->enddate);
            
            DB::commit();
            $response = array('status' => 1, 'title' => "Settlement successfully!!");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Settlement Approval Error', [
                'userid' => $r->userid ?? 'unknown',
                'utr' => $r->utr ?? 'unknown',
                'hold_amount' => $holdAmount ?? 0,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $response = array('status' => 0, 'title' => "Error: " . $e->getMessage());
        }
        
        return response()->json($response);
    }
    public function epin_reedem(Request $r)
    {
        $r->validate([
            "id" => 'required',
        ]);
        $exist = Order::where('pid', $r->id)->first();
        if ($exist) {
            addwallet($exist->userid, $exist->amount, '+', 'wallet');
            addtransaction($exist->userid, 'add_fund', 'credit', $exist->amount, 'by_epin', 1, $exist->pid);
            Order::where('pid', $r->id)->update([
                "status" => 0,
            ]);
            $response = array('status' => 1, 'title' => "E-pin reedem successfully!!");
        } else {
            $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
        }
        return response()->json($response);
    }
    public function AddFundToWallet($userid, $amount)
    {
        Wallet::where('userid', $userid)->update(["wallet" => DB::raw('wallet + ' . floatval($amount))]);
    }
    public function AddFundToPayin($userid, $amount)
    {
        Wallet::where('userid', $userid)->update(["payin" => DB::raw('payin + ' . floatval($amount))]);
    }
    public function p2ptransfer(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'amount' => 'required',
            'transaction_password' => 'required',
        ]);

        $existuser = user::where('userid', $r->userid)->where('package', '>', 0)->where('status', '1')->first();
        if ($existuser) {
            if ($existuser->userid == user('userid')) {
                $response = array('status' => 0, 'title' => "Oops,This is your userid, Use different id!!");
            } else {
                $transpassword = Transaction_password::where('userid', user('userid'))->first();
                if ($transpassword) {
                    if (Hash::check($r->transaction_password, $transpassword->password)) {
                        if ($r->amount > 0 && wallet(user('userid'), 'num', 'wallet') >= $r->amount) {
                            //Main user insert
                            addwallet(user('userid'), $r->amount, '-', 'wallet');
                            addtransaction(user('userid'), 'p2ptransfer', 'debit', $r->amount, $r->userid, 1);
                            //Other user insert
                            addwallet($r->userid, $r->amount, '+', 'wallet');
                            addtransaction($r->userid, 'p2ptransfer', 'credit', $r->amount, user('userid'), 1);
                            $response = array('status' => 1, 'title' => "Fund Transfer Successfully!");
                        } else {
                            $response = array('status' => 0, 'title' => "Insufficient Fund!!");
                        }
                    } else {
                        $response = array('status' => 0, 'title' => "Incorrect Transaction Password!!");
                    }
                } else {
                    $response = array('status' => 0, 'title' => "Create Transaction Password!!");
                }
            }
        } else {
            $response = array('status' => 0, 'title' => "User not found!!");
        }
        return response()->json($response);
    }

    public function donation(Request $r)
    {
        $response = array('status' => 0, 'title' => "Something wen't wrong!!");
        $r->validate([
            "type" => 'required',
            "amount" => 'required',
            "transaction_password" => 'required',
        ]);
        if (checktransactionpassword(user('userid'), $r->transaction_password)) {
            if (wallet(user('userid'), 'num', 'wallet') > 0 && $r->amount > 0 && wallet(user('userid'), 'num', 'wallet') >= $r->wallet) {
                $d = new Donations;
                $d->userid = user('userid');
                $d->type = $r->type;
                $d->amount = $r->amount;
                if ($d->save()) {
                    addwallet(user('userid'), $r->amount, '-', 'wallet');
                    addtransaction(user('userid'), 'donation', 'debit', $r->amount, $r->type, 1);
                    $response = array('status' => 1, 'title' => "Donation Collect Successfully!!");
                }
            } else {
                $response = array('status' => 0, 'title' => "Insufficient Amount!!");
            }
        } else {
            $response = array('status' => 0, 'title' => "Transaction Password Incorrect!!");
        }
        return response()->json($response);
    }
    public function withdrawal_amount(Request $r)
    {
        $response = array('status' => 0, 'title' => "Something wen't wrong!!");
        $r->validate([
            "amount" => 'required',
            "tpassword" => 'required',
        ]);
        // if (user('package') > 0) {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $transaction = Transaction::where('userid', user('userid'))->where('status', '<', 2)->whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        // if (!$transaction || empty($transaction)) {
        if (checktransactionpassword(user('userid'), $r->tpassword)) {
            $finalamount = $r->amount - percentage($r->amount, 10);

            if (wallet(user('userid'), 'num', 'wallet') > 0 && $finalamount > 0 && wallet(user('userid'), 'num', 'wallet') >= $r->amount) {
                if ($r->amount >= setting('min_withdrawal')) {
                    $amount = $r->amount;
                    addwallet(user('userid'), $amount, '-', 'wallet');
                    addtransaction(user('userid'), 'withdrawal', 'debit', $amount, $r->type, 1, $finalamount);
                    $response = array('status' => 1, 'title' => "Withdrawal request registered Successfully!!");
                } else {
                    $response = array('status' => 0, 'title' => "Minimum limit withdrawal is" . setting('min_withdrawal') . "!!");
                }
            } else {
                $response = array('status' => 0, 'title' => "Insufficient Amount!!");
            }
        } else {
            $response = array('status' => 0, 'title' => "Transaction Password Incorrect!!");
        }
        // } else {
        //     $response = array('status' => 0, 'title' => "Only one withdrawal per week!!");
        // }
        // } else {
        //     $response = array('status' => 0, 'title' => "Please purchase any package for withdrawal amount!!");
        // }
        return response()->json($response);
    }
    public function admin_approve_withdrawal(Request $r)
    {
        $r->validate([
            "id" => 'required',
            "utr" => 'required'
        ]);
        $exist = PayoutRequest::where('id', $r->id)->first();
        if ($exist) {
            PayoutRequest::where('id', $r->id)->update([
                "utr" => $r->utr,
                "status" => 1,
            ]);
            $response = array('status' => 1, 'title' => "Success! Withdrawal approve successfully!!");
        } else {
            $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
        }
        return response()->json($response);
    }
    public function admin_cancel_withdrawal(Request $r)
    {
        $r->validate([
            "id" => 'required',
            "remark" => 'required',
        ]);
        $exist = PayoutRequest::where('id', $r->id)->first();
        // return $exist;
        if ($exist && $exist->status != 2) {
            PayoutRequest::where('id', $r->id)->update([
                "remark" => $r->remark,
                "status" => 2,
            ]);
            addwallet($exist->userid, $exist->amount, '+', 'payout');
            addtransaction($exist->userid, 'refund', 'credit', $exist->amount, '', 1);
            $response = array('status' => 1, 'title' => "Success! Withdrawal Cancel successfully!!");
        } else {
            $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
        }
        return response()->json($response);
    }
    // public function admin_approve_withdrawal(Request $r)
    // {
    //     $r->validate([
    //         "id" => 'required',
    //     ]);
    //     $exist = Transaction::where('id', $r->id)->first();
    //     if ($exist) {
    //         Transaction::where('id', $r->id)->update([
    //             "data1" => 'TMPL' . time(),
    //             "status" => 0,
    //         ]);
    //         $response = array('status' => 1, 'title' => "Success! Withdrawal approve successfully!!");
    //     } else {
    //         $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
    //     }
    //     return response()->json($response);
    // }
    // public function admin_cancel_withdrawal(Request $r)
    // {
    //     $r->validate([
    //         "id" => 'required',
    //     ]);
    //     $exist = Transaction::where('id', $r->id)->first();
    //     if ($exist && $exist->status != 2) {
    //         Transaction::where('id', $r->id)->update([
    //             "status" => 2,
    //         ]);
    //         addwallet($exist->userid, $exist->data2, '+', 'wallet');
    //         addtransaction($exist->userid, 'refund', 'credit', $exist->data2, '', 1);
    //         $response = array('status' => 1, 'title' => "Success! Withdrawal Cancel successfully!!");
    //     } else {
    //         $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
    //     }
    //     return response()->json($response);
    // }
    public function admin_epin_expire(Request $r)
    {
        $r->validate([
            "id" => 'required',
        ]);
        $exist = Order::where('pid', $r->id)->first();
        if ($exist) {
            Order::where('pid', $r->id)->update([
                "status" => 0,
            ]);
            $response = array('status' => 1, 'title' => "E-pin expire successfully!!");
        } else {
            $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
        }
        return response()->json($response);
    }
    public function admin_approve_userrequest(Request $r)
    {
        $r->validate([
            "id" => 'required',
        ]);
        $exist = Transaction::where('id', $r->id)->first();
        if ($exist) {
            Transaction::where('id', $r->id)->update([
                "status" => 1,
            ]);
            addwallet($exist->userid, $exist->amount, '+', 'wallet');
            $response = array('status' => 1, 'title' => "Success! User Recharge successfully!!");
        } else {
            $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
        }
        return response()->json($response);
    }
    public function admin_cancel_userrequest(Request $r)
    {
        $r->validate([
            "id" => 'required',
            // "remark" => 'required',
        ]);
        $exist = Transaction::where('id', $r->id)->first();
        if ($exist) {
            Transaction::where('id', $r->id)->update([
                "status" => 2,
            ]);
            $response = array('status' => 1, 'title' => "User Recharge Cancelled !");
        } else {
            $response = array('status' => 0, 'title' => "Oops! Something wents wrong!!");
        }
        return response()->json($response);
    }

    // function decrypt($base64, $Key) {
    //     list($ct, $salt) = decode($base64);
    //     if ($ct == "snderr") {
    //         return false;
    //     }
    //     list($key, $iv) = evpkdf($Key, $salt);
    //     $data = openssl_decrypt($ct, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    //     return $data;
    // }

    function decode($base64)
    {
        $data = base64_decode($base64);
        $ct = "snderr";
        $salt = "snderr";

        if (substr($data, 0, 8) !== "Salted__") {
            return [$ct, $salt];
        }

        $salt = substr($data, 8, 8);
        $ct = substr($data, 16);

        return [$ct, $salt];
    }
    public function gatewayInitiate($amount, $orderid, $mobile, $test = false)
    {
        $instrument = array(
            "type" => "PAY_PAGE",
        );
        if ($test) {
            $instrument = array(
                "type" => "PAY_PAGE",
                "targetApp" => "PHONEPE"
            );
        }
        $merchantId = 'M22MA4PAMRRA2'; // sandbox or test merchantId
        $apiKey = "b5ac135e-cce1-4be0-8f87-6a6dc72e9971"; // sandbox or test APIKEY
        $callbackUrl = url('api/pg/phonepe/recharge_successfully');
        $redirectUrl = url('api/pg/phonepe/redirect_recharge_successfully');
        $name = "Mother Solution User";
        $email = "void@merchant.rudraxpay.com";
        $description = 'Purchase or Maintain Services';
        $order_id = $orderid;
        $paymentData = array(
            'merchantId' => $merchantId,
            'merchantTransactionId' => $order_id, // test transactionID
            "merchantUserId" => "MUID123",
            'amount' => $amount * 100,
            'redirectUrl' => $redirectUrl,
            'redirectMode' => "POST",
            'callbackUrl' => $callbackUrl,
            "merchantOrderId" => $order_id,
            "mobileNumber" => $mobile,
            "message" => $description,
            "email" => $email,
            "shortName" => $name,
            "deviceContext" => array("deviceOS" => "ANDROID"),
            "paymentInstrument" => $instrument
        );
        $jsonencode = json_encode($paymentData);
        $payloadMain = base64_encode($jsonencode);
        $salt_index = 1; //key index 1
        $payload = $payloadMain . "/pg/v1/pay" . $apiKey;
        $sha256 = hash("sha256", $payload);
        $final_x_header = $sha256 . '###' . $salt_index;
        $request = json_encode(array('request' => $payloadMain));
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.phonepe.com/apis/hermes/pg/v1/pay",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "X-VERIFY: " . $final_x_header,
                "accept: application/json",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($response);
            // if(isset($res->success) && $res->success=='1'){
            //     $paymentCode=$res->code;
            //     $paymentMsg=$res->message;
            //     $payUrl=$res->data->instrumentResponse->redirectInfo->url;

            //     header('Location:'.$payUrl) ;
            // }
        }
    }
    public function UPITelGatewayInitiate($amount, $orderid, $mobile)
    {
        $redirectUrl = url('api/pg/upitel/redirect_recharge_successfully/' . $orderid);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.ekqr.in/api/create_order',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            //   CURLOPT_POSTFIELDS =>' {
            //          "loginid": "7060471592",
            //          "apikey":"p7zyejnxal",
            //          "orderid": "'.$orderid.'",
            //          "amt": "'.$amount.'",
            //          "trxnote": "Payment For Internship",
            //          "custmobile": "'.$mobile.'",
            //          "redirecturl": "'.$redirectUrl.'"
            CURLOPT_POSTFIELDS => '{
  "key": "663ce730-dac1-414c-9d38-96d4e0bda46a",
  "client_txn_id": "' . $orderid . '",
  "amount": "' . $amount . '",
  "p_info": "Payment For Internship",
  "customer_name": "Payment For Internship",
  "customer_email": "info@gmail.com",
  "customer_mobile": "7060471592",
  "redirect_url": "' . $redirectUrl . '",
  "udf1": "user defined field 1 (max 25 char)",
  "udf2": "user defined field 2 (max 25 char)",
  "udf3": "user defined field 3 (max 25 char)"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    public function walletbalance(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'token' => 'required'
        ]);
        if (!isset($r->userid) || $r->userid == "") {
            return response()->json(["status" => "failed", "message" => "Userid Mandatory"]);
        }
        if (!isset($r->token) || $r->token == "") {
            return response()->json(["status" => "failed", "message" => "Token Mandatory"]);
        }

        $UserData = user::where('userid', $r->userid)->first();
        if (!isset($UserData) || !$UserData)
            return response()->json(["status" => false, "message" => "Userid invalid!"]);
        if ($UserData->status != 1)
            return response()->json(["status" => false, "message" => "Userid Blocked!"]);
        if ($UserData->token != $r->token)
            return response()->json(["status" => false, "message" => "Invalid Token!"]);
        if ($UserData->out_ip != $r->ip())
            return response()->json(array('status' => false, 'message' => "IP Restricted", "your_ip" => $r->ip()));

        $wallet = Wallet::where("userid", $UserData->userid)->first();
        return response()->json(array('status' => true, 'message' => "success", "payin" => $wallet->payin, "payout" => $wallet->payout, "aeps" => $wallet->wallet));
    }
    public function phonepe_checkstatus(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'orderid' => 'required'
        ]);
        if (!isset($r->userid) || $r->userid == "") {
            return response()->json(["status" => "failed", "message" => "Userid Mandatory"]);
        }
        if (!isset($r->orderid) || $r->orderid == "") {
            return response()->json(["status" => "failed", "message" => "OrderId Mandatory"]);
        }
        $exist = Payment_request::where('transaction_id', $r->orderid)->where('userid', $r->userid)->first();
        if (!$exist) {
            return response()->json(["status" => "failed", "message" => "Invalid Order Id!!"]);
        }
        
        $PaymentStatus = "";
        $Status = "failed";
        $gatewayFlag = $exist->data6 ?? null;
        
        // Always check with gateway provider API first, regardless of local status
        // This ensures we get the latest status from the provider
        $gatewayChecked = false;
        
        try {
            // ✅ EASEBUZZ (FLAG 28) - Check status via Easebuzz API
            if ($gatewayFlag == 28 || $gatewayFlag == '28') {
                $gatewayChecked = true;
                $easebuzz = new \App\Http\Controllers\Gateway\Easebuzz();
                $statusRequest = new \Illuminate\Http\Request();
                $statusRequest->merge(['order_id' => $r->orderid]);
                
                $gatewayResponse = $easebuzz->checkStatus($statusRequest);
                $gatewayData = json_decode($gatewayResponse->getContent(), true);
                
                if (isset($gatewayData['status']) && $gatewayData['status'] == 1) {
                    // Payment successful at gateway
                    // Only update if local status is different
                    if ($exist->status != 1) {
                        $exist->status = 1;
                        $exist->data1 = $gatewayData['bank_ref_num'] ?? $gatewayData['easepayid'] ?? '';
                        $exist->data2 = $gatewayData['easepayid'] ?? '';
                        $exist->save();
                        
                        // Calculate final amount after tax
                        $finalAmount = $exist->amount - $exist->tax;
                        
                        // Add to payin wallet (only if not already credited)
                        addwallet($exist->userid, $finalAmount, '+', 'payin');
                        
                        // Add transaction record (only if not already exists)
                        addtransaction($exist->userid, 'payin', 'credit', $finalAmount, 'Easebuzz', 1, $exist->data1);
                    }
                    
                    $Status = "success";
                    $PaymentStatus = "Payment successful";
                } elseif (isset($gatewayData['status']) && $gatewayData['status'] == 0) {
                    // Payment failed at gateway
                    if ($exist->status != 2) {
                        $exist->status = 2;
                        $exist->data1 = 'FAILED';
                        $exist->data2 = $gatewayData['error_Message'] ?? 'Payment failed';
                        $exist->save();
                    }
                    
                    $Status = "failed";
                    $PaymentStatus = $gatewayData['error_Message'] ?? "Payment failed";
                } else {
                    // Gateway returned unknown status or pending
                    $Status = "pending";
                    $PaymentStatus = "Bank side pending";
                }
            }
            
            // ✅ AUROPAY (FLAG 29) - Check status via AuroPay API
            elseif ($gatewayFlag == 29 || $gatewayFlag == '29') {
                $gatewayChecked = true;
                $auropay = new \App\Http\Controllers\Gateway\AuroPay();
                
                // Get AuroPay transaction ID from data1 or check callback payload
                $auropayTxnId = $exist->data1;
                if (empty($auropayTxnId)) {
                    // Try to get from callback payload
                    $payload = $exist->callback_payload;
                    if (is_string($payload)) {
                        $payload = json_decode($payload, true);
                    }
                    if (is_array($payload)) {
                        $auropayTxnId = $payload['TransactionId'] ?? $payload['id'] ?? null;
                    }
                }
                
                if ($auropayTxnId) {
                    $statusData = $auropay->checkTransactionStatus($auropayTxnId);
                    
                    if ($statusData && isset($statusData['transactionStatus'])) {
                        $auropayStatus = $statusData['transactionStatus'];
                        
                        // AuroPay status: 2 = Authorized, 16 = Success
                        if ($auropayStatus == 2 || $auropayStatus == 16) {
                            // SUCCESS at gateway
                            // Only update if local status is different
                            if ($exist->status != 1) {
                                $exist->status = 1;
                                $exist->data1 = $auropayTxnId;
                                $exist->data2 = $statusData['processorName'] ?? '';
                                
                                $utr = $statusData['traceNumber'] ?? $statusData['processorRefId'] ?? '';
                                if (!empty($utr)) {
                                    $exist->data4 = $utr;
                                }
                                
                                // Update callback payload manually (since enrichPaymentRequestPayload is private)
                                $existingPayload = $exist->callback_payload;
                                if (is_string($existingPayload)) {
                                    $existingPayload = json_decode($existingPayload, true);
                                }
                                if (!is_array($existingPayload)) {
                                    $existingPayload = [];
                                }
                                
                                $existingPayload['transactionStatus'] = $statusData['transactionStatus'] ?? null;
                                $existingPayload['traceNumber'] = $statusData['traceNumber'] ?? null;
                                $existingPayload['processorRefId'] = $statusData['processorRefId'] ?? null;
                                $existingPayload['transactionId'] = $statusData['transactionId'] ?? null;
                                $existingPayload['processor_name'] = $statusData['processorName'] ?? null;
                                $existingPayload['auropay_status'] = $statusData;
                                
                                $exist->callback_payload = $existingPayload;
                                $exist->save();
                                
                                // Calculate final amount after tax
                                $finalAmount = $exist->amount - $exist->tax;
                                
                                // Add to payin wallet (only if not already credited)
                                addwallet($exist->userid, $finalAmount, '+', 'payin');
                                
                                // Add transaction record (only if not already exists)
                                addtransaction($exist->userid, 'payin', 'credit', $finalAmount, 'AuroPay', 1, $auropayTxnId);
                            }
                            
                            $Status = "success";
                            $PaymentStatus = "Payment successful";
                        } else {
                            // FAILED at gateway
                            if ($exist->status != 2) {
                                $exist->status = 2;
                                $exist->data1 = $auropayTxnId;
                                $exist->data2 = 'Status: ' . $auropayStatus;
                                $exist->save();
                            }
                            
                            $Status = "failed";
                            $PaymentStatus = "Payment failed";
                        }
                    } else {
                        // Gateway didn't return valid status data
                        $Status = "pending";
                        $PaymentStatus = "Bank side pending";
                    }
                } else {
                    // Transaction ID not found - can't check with gateway
                    $Status = "pending";
                    $PaymentStatus = "Waiting for gateway response - Transaction ID not found";
                }
            }
            
            // If gateway is not Easebuzz or AuroPay, fall back to local status
            if (!$gatewayChecked) {
        if ($exist->status == 1) {
            $Status = "success";
                    $PaymentStatus = "Payment successful";
        } elseif ($exist->status == 2) {
            $Status = "failed";
                    $PaymentStatus = "Payment failed";
        } else {
            $Status = "pending";
            $PaymentStatus = "Bank side pending";
        }
            }
            
        } catch (\Exception $e) {
            \Log::error('Gateway Status Check Error', [
                'orderid' => $r->orderid,
                'gateway' => $gatewayFlag,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // On error, fall back to local status
            if ($exist->status == 1) {
                $Status = "success";
                $PaymentStatus = "Payment successful (local status - gateway check failed)";
            } elseif ($exist->status == 2) {
                $Status = "failed";
                $PaymentStatus = "Payment failed (local status - gateway check failed)";
            } else {
                $Status = "pending";
                $PaymentStatus = "Error checking status: " . $e->getMessage();
            }
        }
        
        // Determine gateway name
        $gatewayName = "Unknown";
        if ($gatewayFlag == 28 || $gatewayFlag == '28') {
            $gatewayName = "Easebuzz";
        } elseif ($gatewayFlag == 29 || $gatewayFlag == '29') {
            $gatewayName = "AuroPay";
        }
        
        return response()->json([
            "status" => $Status, 
            "message" => $PaymentStatus, 
            "utr" => $exist->data1 ?? '', 
            "client_txn_id" => $exist->transaction_id, 
            "amount" => $exist->amount,
            "gateway" => $gatewayName
        ]);
    }
    
    /**
     * Check and update pending transactions directly from gateway providers
     * This function directly calls Easebuzz and AuroPay APIs to check status
     * and updates the database accordingly
     */
    public function check_pending_transactions(Request $r)
    {
        $r->validate([
            'userid' => 'required'
        ]);
        
        $userid = $r->userid;
        $user = user::where('userid', $userid)->first();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ]);
        }
        
        // Get pending transactions (status = 0) older than 5 minutes
        $pendingTxns = Payment_request::where('userid', $userid)
            ->where('status', 0)
            ->where('created_at', '<=', now()->subMinutes(5))
            ->whereIn('data6', [28, 29, '28', '29']) // Only Easebuzz and AuroPay
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();
        
        if ($pendingTxns->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No pending transactions found',
                'checked' => 0,
                'updated' => 0
            ]);
        }
        
        $checked = 0;
        $updated = 0;
        $failed = 0;
        $results = [];
        
        foreach ($pendingTxns as $txn) {
            $checked++;
            $gatewayFlag = $txn->data6 ?? null;
            
            try {
                // ✅ EASEBUZZ (FLAG 28) - Check directly with Easebuzz API
                if ($gatewayFlag == 28 || $gatewayFlag == '28') {
                    $easebuzz = new \App\Http\Controllers\Gateway\Easebuzz();
                    $statusRequest = new \Illuminate\Http\Request();
                    $statusRequest->merge(['order_id' => $txn->transaction_id]);
                    
                    $gatewayResponse = $easebuzz->checkStatus($statusRequest);
                    $gatewayData = json_decode($gatewayResponse->getContent(), true);
                    
                    if (isset($gatewayData['status']) && $gatewayData['status'] == 1) {
                        // Payment successful at provider
                        if ($txn->status != 1) {
                            $txn->status = 1;
                            $txn->data1 = $gatewayData['bank_ref_num'] ?? $gatewayData['easepayid'] ?? '';
                            $txn->data2 = $gatewayData['easepayid'] ?? '';
                            $txn->save();
                            
                            // Calculate final amount after tax
                            $finalAmount = $txn->amount - $txn->tax;
                            
                            // Add to payin wallet
                            addwallet($txn->userid, $finalAmount, '+', 'payin');
                            
                            // Add transaction record
                            addtransaction($txn->userid, 'payin', 'credit', $finalAmount, 'Easebuzz', 1, $txn->data1);
                            
                            $updated++;
                            $results[] = [
                                'txn_id' => $txn->transaction_id,
                                'status' => 'updated_to_success',
                                'amount' => $finalAmount,
                                'gateway' => 'Easebuzz',
                                'utr' => $txn->data1
                            ];
                        }
                    } elseif (isset($gatewayData['status']) && $gatewayData['status'] == 0) {
                        // Payment failed at provider
                        if ($txn->status != 2) {
                            $txn->status = 2;
                            $txn->data1 = 'FAILED';
                            $txn->data2 = $gatewayData['error_Message'] ?? 'Payment failed';
                            $txn->save();
                            
                            $failed++;
                            $results[] = [
                                'txn_id' => $txn->transaction_id,
                                'status' => 'marked_failed',
                                'gateway' => 'Easebuzz',
                                'reason' => $gatewayData['error_Message'] ?? 'Payment failed'
                            ];
                        }
                    } else {
                        // Still pending at provider
                        $results[] = [
                            'txn_id' => $txn->transaction_id,
                            'status' => 'still_pending',
                            'gateway' => 'Easebuzz',
                            'message' => 'Bank side pending'
                        ];
                    }
                }
                
                // ✅ AUROPAY (FLAG 29) - Check directly with AuroPay API
                elseif ($gatewayFlag == 29 || $gatewayFlag == '29') {
                    $auropay = new \App\Http\Controllers\Gateway\AuroPay();
                    
                    // Get AuroPay transaction ID from data1 or callback payload
                    $auropayTxnId = $txn->data1;
                    if (empty($auropayTxnId)) {
                        $payload = $txn->callback_payload;
                        if (is_string($payload)) {
                            $payload = json_decode($payload, true);
                        }
                        if (is_array($payload)) {
                            $auropayTxnId = $payload['TransactionId'] ?? $payload['id'] ?? null;
                        }
                    }
                    
                    if ($auropayTxnId) {
                        // Call AuroPay API directly
                        $statusData = $auropay->checkTransactionStatus($auropayTxnId);
                        
                        if ($statusData && isset($statusData['transactionStatus'])) {
                            $auropayStatus = $statusData['transactionStatus'];
                            
                            // AuroPay status: 2 = Authorized, 16 = Success
                            if ($auropayStatus == 2 || $auropayStatus == 16) {
                                // Payment successful at provider
                                if ($txn->status != 1) {
                                    $txn->status = 1;
                                    $txn->data1 = $auropayTxnId;
                                    $txn->data2 = $statusData['processorName'] ?? '';
                                    
                                    $utr = $statusData['traceNumber'] ?? $statusData['processorRefId'] ?? '';
                                    if (!empty($utr)) {
                                        $txn->data4 = $utr;
                                    }
                                    
                                    // Update callback payload
                                    $existingPayload = $txn->callback_payload;
                                    if (is_string($existingPayload)) {
                                        $existingPayload = json_decode($existingPayload, true);
                                    }
                                    if (!is_array($existingPayload)) {
                                        $existingPayload = [];
                                    }
                                    
                                    $existingPayload['transactionStatus'] = $statusData['transactionStatus'] ?? null;
                                    $existingPayload['traceNumber'] = $statusData['traceNumber'] ?? null;
                                    $existingPayload['processorRefId'] = $statusData['processorRefId'] ?? null;
                                    $existingPayload['transactionId'] = $statusData['transactionId'] ?? null;
                                    $existingPayload['processor_name'] = $statusData['processorName'] ?? null;
                                    $existingPayload['auropay_status'] = $statusData;
                                    
                                    $txn->callback_payload = $existingPayload;
                                    $txn->save();
                                    
                                    // Calculate final amount after tax
                                    $finalAmount = $txn->amount - $txn->tax;
                                    
                                    // Add to payin wallet
                                    addwallet($txn->userid, $finalAmount, '+', 'payin');
                                    
                                    // Add transaction record
                                    addtransaction($txn->userid, 'payin', 'credit', $finalAmount, 'AuroPay', 1, $auropayTxnId);
                                    
                                    $updated++;
                                    $results[] = [
                                        'txn_id' => $txn->transaction_id,
                                        'status' => 'updated_to_success',
                                        'amount' => $finalAmount,
                                        'gateway' => 'AuroPay',
                                        'utr' => $utr
                                    ];
                                }
                            } else {
                                // Payment failed at provider
                                if ($txn->status != 2) {
                                    $txn->status = 2;
                                    $txn->data1 = $auropayTxnId;
                                    $txn->data2 = 'Status: ' . $auropayStatus;
                                    $txn->save();
                                    
                                    $failed++;
                                    $results[] = [
                                        'txn_id' => $txn->transaction_id,
                                        'status' => 'marked_failed',
                                        'gateway' => 'AuroPay',
                                        'reason' => 'Status: ' . $auropayStatus
                                    ];
                                }
                            }
                        } else {
                            // Provider didn't return valid status
                            $results[] = [
                                'txn_id' => $txn->transaction_id,
                                'status' => 'still_pending',
                                'gateway' => 'AuroPay',
                                'message' => 'Bank side pending'
                            ];
                        }
                    } else {
                        // Transaction ID not found
                        $results[] = [
                            'txn_id' => $txn->transaction_id,
                            'status' => 'error',
                            'gateway' => 'AuroPay',
                            'error' => 'Transaction ID not found'
                        ];
                    }
                }
                
            } catch (\Exception $e) {
                \Log::error('Check Pending Transaction Error', [
                    'txn_id' => $txn->transaction_id,
                    'gateway' => $gatewayFlag,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $results[] = [
                    'txn_id' => $txn->transaction_id,
                    'status' => 'error',
                    'gateway' => $gatewayFlag == 28 ? 'Easebuzz' : ($gatewayFlag == 29 ? 'AuroPay' : 'Unknown'),
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'status' => true,
            'message' => "Checked {$checked} transactions from gateway providers",
            'checked' => $checked,
            'updated' => $updated,
            'failed' => $failed,
            'still_pending' => $checked - $updated - $failed,
            'results' => $results
        ]);
    }
    
    public function phonepe_initiateTest(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'token' => 'required',
            'amount' => 'required',
            'mobile' => 'required',
            'callback_url' => 'required',
            'orderid' => 'required',
        ]);
        if ($r->amount >= 1) {
            $exist = Payment_request::where('transaction_id', $r->orderid)->first();
            if ($exist) {
                return response()->json(["status" => false, "message" => "Dublicate entry!!"]);
            }
            $charge = ($r->amount / 100) * user('percentage', $r->userid);
            $gst = ($charge / 100) * 18;
            $trn_id = $r->orderid;
            $payment_trn = new Payment_request;
            $payment_trn->transaction_id = $trn_id;
            $payment_trn->userid = $r->userid;
            $payment_trn->amount = $r->amount;
            $payment_trn->tax = $charge + $gst;
            $payment_trn->callbackurl = $r->callback_url;
            $payment_trn->data3 = 1;
            $payment_trn->status = 0;
            if ($payment_trn->save()) {
                // $dataArray = array($r->amount,$trn_id,$r->mobile);
                // $encryptedMessage = Crypt::encrypt(json_encode($dataArray));
                // return response()->json(["status"=>true,"message"=>"Link successfully generated","url"=>"https://merchant.rudraxpay.com/api/pg/phonepe/token/".$encryptedMessage]);

                $flag = 1;//1=phonepe,2=upitel
                $token_value = "#";
                if ($flag == 1) {
                    return $res = $this->gatewayInitiate($r->amount, $trn_id, $r->mobile, true);
                    if (isset($res->success) && $res->success == '1') {
                        $paymentCode = $res->code;
                        $paymentMsg = $res->message;
                        $url = $res->data->instrumentResponse->redirectInfo->url;
                        // Parse the URL
                        $parsed_url = parse_url($url);
                        // Parse the query parameters
                        parse_str($parsed_url['query'], $query_params);
                        // Get and display the value of the 'token' parameter
                        $token_value = $query_params['token'] ?? null;
                    } else {
                        // return $res;
                    }
                } elseif ($flag == 2) {
                    $res = $this->UPITelGatewayInitiate($r->amount, $trn_id, $r->mobile);
                    // return $res->status;
                    if (isset($res->status) && $res->status == 'success') {
                        $token_value = $res->gotourl;
                    } else {
                        // return $res;
                    }
                }
                $Amount = $r->amount;
                $IntentUrl = "upi://pay?pa=M22MA4PAMRRA2@ybl&pn=MPay&am=$Amount.00&mam=$Amount.00&tr=$trn_id&tn=Payment%20for%20$trn_id&mc=4816&mode=04&purpose=00&utm_campaign=B2B_PG&utm_medium=M22MA4PAMRRA2&utm_source=$trn_id";
                $encryptedMessage = Crypt::encrypt($token_value);
                return response()->json(["status" => true, "message" => "Link successfully generated", "INTENTURL" => $res, "message" => "Intent active", "url" => "https://merchant.rudraxpay.com/api/pg/phonepe/token/" . $encryptedMessage . "/" . $flag]);
                return response()->json(["status" => false, "message" => "Something wents wrong"]);
            }
        } else {
            $response = array('status' => false, 'message' => "Amount should be at least ₹1!!");
        }
        return response()->json($response);
    }
    public function CCAvenueGatewayIniatiate($amount, $orderid, $mobile, $name = 'Pushpendra technology User')
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pushpendratechnology.com/ccavenue/ccavRequestHandler.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('orderid' => $orderid, 'amount' => $amount, 'mobile' => $mobile, 'name' => $name),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    public function isMultipleOf1K($number)
    {
        // Check if the number is a multiple of 1000
        if ($number % 1000 === 0) {
            return true;
        } else {
            return false;
        }
    }
    public function PaymentPageMerchant($userid)
    {
        $exist = $UserData = user::where('userid', $userid)->first();
        if (!$exist) {
            return "Access Denied!";
        }

    }
    public function phonepe_initiate_non_seamless(Request $r)
    {
        $json_string = json_encode($r->all());
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'token' => 'required',
            'amount' => 'required',
            'mobile' => 'required',
            'callback_url' => 'required',
            'orderid' => 'required',
        ]);
        //Validations
        if (!isset($r->userid) || $r->userid == "")
            return response()->json(["status" => false, "message" => "Userid is required!!"]);
        if (!isset($r->token) || $r->token == "")
            return response()->json(["status" => false, "message" => "Token is required!!"]);
        if (!isset($r->amount) || $r->amount == "")
            return response()->json(["status" => false, "message" => "Amount is required!!"]);
        if (!isset($r->orderid) || $r->orderid == "")
            return response()->json(["status" => false, "message" => "OrderId is required!!"]);
        if (!isset($r->mobile) || $r->mobile == "")
            return response()->json(["status" => false, "message" => "Mobile is required!!"]);
        // if(!isset($r->name) || $r->name == "")
        // return response()->json(["status"=>false,"message"=>"Name is required!!"]);
        $UserData = user::where('userid', $r->userid)->first();
        if (!isset($UserData) || !$UserData)
            return response()->json(["status" => false, "message" => "Userid invalid!"]);
        if ($UserData->status != 1)
            return response()->json(["status" => false, "message" => "Userid Blocked!"]);
        if ($UserData->token != $r->token)
            return response()->json(["status" => false, "message" => "Invalid Token!"]);
        if ($UserData->callback == "")
            return response()->json(["status" => false, "message" => "Service not active!"]);
        if ($r->amount > 10) {
            $exist = Payment_request::where('transaction_id', $r->orderid)->first();
            if ($exist) {
                return response()->json(["status" => false, "message" => "Dublicate entry!!"]);
            }
            $charge = ($r->amount / 100) * user('percentage', $r->userid);
            $gst = ($charge / 100) * 18;
            $trn_id = $r->orderid;
            $flag = setting('gateway');//1=phonepe,2=upitel,3=ccavenue,4=Cashfree,5=MannualBharatPe,6=Razorpay,7=Sabpaisa,8=Payomatix,9=Runpaisa,10=USDPAY
            // if($r->amount >= 100){

            // }
            if ($flag == 9) {
                if ($this->isMultipleOf1K($r->amount)) {
                    $r->amount = $r->amount + 1;
                }
            }
            $payment_trn = new Payment_request;
            $payment_trn->transaction_id = $trn_id;
            $payment_trn->userid = $r->userid;
            $payment_trn->amount = $r->amount;
            $payment_trn->mobile = $r->mobile;
            $payment_trn->name = $r->name;
            $payment_trn->tax = $charge + $gst;
            $payment_trn->callbackurl = $r->callback_url;
            $payment_trn->data3 = 1;
            $payment_trn->status = 0;
            if ($payment_trn->save()) {
                $token_value = "#";
                if ($flag == 1) {
                    $res = $this->gatewayInitiate($r->amount, $trn_id, $r->mobile);
                    if (isset($res->success) && $res->success == '1') {
                        $paymentCode = $res->code;
                        $paymentMsg = $res->message;
                        $url = $res->data->instrumentResponse->redirectInfo->url;
                        // Parse the URL
                        $parsed_url = parse_url($url);
                        // Parse the query parameters
                        parse_str($parsed_url['query'], $query_params);
                        // Get and display the value of the 'token' parameter
                        $token_value = $query_params['token'] ?? null;
                    }
                } elseif ($flag == 2) {
                    $res = $this->UPITelGatewayInitiate($r->amount, $trn_id, $UserData->mobile, $UserData->name);
                    if (isset($res->status) && $res->status == 'success') {
                        $token_value = $res->gotourl;
                    }
                } elseif ($flag == 3) {
                    $res = $this->CCAvenueGatewayIniatiate($r->amount, $trn_id, $r->mobile);
                    if (isset($res->intentUrl) && $res->intentUrl != "") {
                        $token_value = $res->intentUrl;
                        $token_value = str_replace("Pushpendra Technology Pvt Ltd", "ptpl", $token_value);
                    }
                } elseif ($flag == 4) {
                    $token_value = "https://www.pushpendratechnology.com/cashfree/?trn=" . $trn_id . "&mob=" . $r->mobile . "&am=" . $r->amount;
                } elseif ($flag == 5) {
                    $upid = "BHARATPE.8001823795@fbpe";
                    $name = "ADARSH PUSHPENDRA PANDEY";
                    $orderid = $trn_id;
                    $amount = $r->amount;
                    // $token_valuev = "upi://pay?pa=Q827260597@ybl&pn=PhonePeMerchant&pn=$orderid&mc=0000&mode=02&purpose=00&am=1&tr=$orderid&tn=Pay+to+$orderid";
                    $token_valuev = "upi://pay?pa=$upid&pn=$orderid&cu=INR&am=$amount&mc=46956131&mode=04&cu=INR&tr=$orderid&tn=Pay+to+$orderid";
                    $token_value = json_encode(array($token_valuev, $trn_id));
                } elseif ($flag == 6) {
                    $token_value = url("/api/payin/razorpay/Initate/" . $trn_id);
                } elseif ($flag == 7) {
                    $token_value = url("/api/payin/sabpaisa/Initate/" . $trn_id);
                } elseif ($flag == 8) {
                    $token_value = url("/api/payin/payomatix/Initate/" . $trn_id);
                } elseif ($flag == 9) {
                    $token_value = url("/api/payin/runpaisa/Initate/" . $trn_id);
                } elseif ($flag == 10) {
                    $token_value = url("/api/payin/runpaisa/Initate/" . $trn_id);
                }
                $Amount = $r->amount;
                $encryptedMessage = Crypt::encrypt($token_value);
                return redirect("https://merchant.rudraxpay.com/api/pg/phonepe/token/" . $encryptedMessage . "/" . $flag);
                // return response()->json(["status"=>true,"message"=>"Link successfully generated","url"=>"https://merchant.rudraxpay.com/api/pg/phonepe/token/".$encryptedMessage."/".$flag]);
            }
        } else {
            return back()->with('error', "Amount should be at least ₹1!!");
        }
        return response()->json($response);
    }
    const OPENSSL_CIPHER_NAME = "aes-128-cbc";
    const CIPHER_KEY_LEN = 16;
    private function fixKey($key)
    {
        if (strlen($key) < self::CIPHER_KEY_LEN) {
            return str_pad($key, self::CIPHER_KEY_LEN, "0");
        }
        if (strlen($key) > self::CIPHER_KEY_LEN) {
            return substr($key, 0, self::CIPHER_KEY_LEN);
        }
        return $key;
    }

    private function encrypt($key, $iv, $data)
    {
        $encodedEncryptedData = base64_encode(openssl_encrypt($data, self::OPENSSL_CIPHER_NAME, $this->fixKey($key), OPENSSL_RAW_DATA, $iv));
        $encodedIV = base64_encode($iv);
        return $encodedEncryptedData . ":" . $encodedIV;
    }
    public function decryption($key, $iv, $encryptedData)
    {
        $parts = explode(':', $encryptedData);
        if (count($parts) !== 2) {
            return false; // Invalid format check
        }

        $encrypted = $parts[0];

        $iv = $parts[1];

        $decryptedData = openssl_decrypt(base64_decode($encrypted), self::OPENSSL_CIPHER_NAME, $this->fixKey($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $decryptedData;
    }

    private function SabpaisaInitaiteResponse($trn, $am, $name, $mob)
    {
        //         $clientCode = 'MUDR87';
// $username = 'rahulkumartiwari8750@gmail.com';
// $password = 'MUDR87_SP20904';
// $authKey = 'y5HEnNMsIm14n0HJ';
// $authIV = 'z7BXwJuvCRvA9sj3';

        $clientCode = 'MUDR87';
        $username = 'rahulkumartiwari8750@gmail.com';
        $password = 'MUDR87_SP20904';
        $authKey = 'y5HEnNMsIm14n0HJ';
        $authIV = 'z7BXwJuvCRvA9sj3';


        // Transaction details
        $payerName = $name;
        $payerEmail = 'Test@email.in';
        $payerMobile = $mob;
        $payerAddress = 'Patna, Bihar';

        $clientTxnId = $trn;
        $amount = $am;
        $amountType = 'INR';
        $mcc = 5137;
        $channelId = 'W';
        $callbackUrl = url('api/pg/phonepe/redirect_recharge_successfully?transactionId=' . $trn);
        $byPassFlag = 'true';
        $modeTransfer = 'UPI_APPS_MODE_TRANSFER';
        $seamlessType = 'S2S';
        $f = "#";
        $encData = "clientCode=" . $clientCode . "&transUserName=" . $username . "&transUserPassword=" . $password . "&payerName=" . $payerName .
            "&payerMobile=" . $payerMobile . "&payerEmail=" . $payerEmail . "&payerAddress=" . $payerAddress . "&clientTxnId=" . $clientTxnId .
            "&amount=" . $amount . "&amountType=" . $amountType . "&mcc=" . $mcc . "&channelId=" . $channelId . "&callbackUrl=" . $callbackUrl .
            "&browserDetails=English|24-bit|1080|1920|UTC+2" . "&modeTransfer=" . $modeTransfer . "&byPassFlag=" . $byPassFlag .
            "&seamlessType=" . $seamlessType;

        $encryptedData = $this->encrypt($authKey, $authIV, $encData);
        $base_url = "https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1";
        $form_data = [
            "encData" => $encryptedData,
            "clientCode" => $clientCode
        ];

        $headers = [
            "Content-Type: application/x-www-form-urlencoded"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            // return $response;
            $responseData = json_decode($response, true);
            // echo $response;
            //  if (isset($responseData['data'])) {
            //     parse_str($responseData['data'], $parsedData);


            //     $enc = str_replace(' ', '+', $parsedData['encData']);
            //     // print_r($enc);

            //     $f = $this->decryption($authKey,$authIV,  $enc);

            //     print_r($f);

            // } else {
            //     echo "No encrypted response data found.";
            // }

            if (isset($responseData['data'])) {
                parse_str($responseData['data'], $parsedData);
                // $f = ($parsedData);
                $enc = str_replace(' ', '+', $parsedData['encData']);
                // return $parsedData['encData'];
                if (isset($enc)) {
                    $decryptedResponse = $this->decryption($authKey, $authIV, $enc);
                    // return $decryptedResponse;
                    if ($decryptedResponse) {
                        // echo json_encode($decryptedResponse);
                        $f = $decryptedResponse;
                    } else {
                        $f = "Decryption failed.";
                    }
                } else {
                    $f = "No encData found in response.";
                }
            } else {
                $f = "#";
            }
            return $f;


            // if (isset($responseData['data'])) {
            //     parse_str($responseData['data'], $parsedData);
            //     $enc = str_replace(' ', '+', $parsedData['encData']);
            //     print_r($enc);

            //     $decryptedResponse = $this->decrypt($authKey,$authIV,  $enc);

            //     print_r($decryptedResponse);

            // } else {
            //     echo "No encrypted response data found.";
            // }
        }
    }

    private function nsdlInitaiteResponse($trn, $amount, $name, $mobile)
    {
        $apiUrl = "https://apiv2.diaspay.in/api/createTransaction";
        $callbackUrl = "https://merchant.rudraxpay.com/api/payin/nsdl/callback";
        $token = "8|vJci9fYoARR4PBRjQeiz40YzKUxGfONf824WEoz6";
        $userUuid = "swp_sm_8c5762f6-61b4-4705-9e27-a710fe43edfe";

        $postData = http_build_query([
            'amount' => $amount,
            'description' => 'PaymentForService',
            'name' => $name,
            'email' => 'info@rudraxpay.com',
            'mobile' => $mobile,
            'user_uuid' => $userUuid,
            'enabledModesOfPayment' => 'upi',
            'payment_method' => 'UPI_INTENT',
            'callback_url' => $callbackUrl,
            'source' => 'api',
            'encrypt_response' => '0'
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            // Log or handle cURL error
            return ['status' => false, 'message' => "cURL Error: $error_msg"];
        }

        curl_close($ch);

        // Optional: parse response if needed
        $decodedResponse = json_decode($response, true);

        return [
            'status' => $httpCode === 200,
            'http_code' => $httpCode,
            'response' => $decodedResponse ?: $response
        ];
    }

    //     private function SabpaisaInitaiteResponse($trn,$am,$name,$mob){
//         session_start();

    // const OPENSSL_CIPHER_NAME = "aes-128-cbc";
// const CIPHER_KEY_LEN = 16;

    // function fixKey($key) {
//     if (strlen($key) < CIPHER_KEY_LEN) {
//         return str_pad($key, CIPHER_KEY_LEN, "0");
//     }
//     if (strlen($key) > CIPHER_KEY_LEN) {
//         return substr($key, 0, CIPHER_KEY_LEN);
//     }
//     return $key;
// }

    // function encrypt($key, $iv, $data) {
//     $encodedEncryptedData = base64_encode(openssl_encrypt($data, OPENSSL_CIPHER_NAME, fixKey($key), OPENSSL_RAW_DATA, $iv));
//     $encodedIV = base64_encode($iv);
//     return $encodedEncryptedData . ":" . $encodedIV;
// }

    // function decrypt($key,$iv, $encryptedData) {
//     $parts = explode(':', $encryptedData);
//     if (count($parts) !== 2) {
//         return false; // Invalid format check
//     }

    //     $encrypted = $parts[0];

    //     $iv = $parts[1];

    //     $decryptedData = openssl_decrypt(base64_decode($encrypted), OPENSSL_CIPHER_NAME, fixKey($key), OPENSSL_RAW_DATA, base64_decode($iv));
//     return $decryptedData;


    // }

    // // API credentials
// // $clientCode = 'HAR9I9';  
// // $username = 'vijaygupta8991_19811';
// // $password = 'HAR9I9_SP19811';
// // $authKey = 'cT06ZaSMESCr8Xsi';
// // $authIV = 'cgmmWsnZ65pjTWYu';

    // $clientCode = 'MUDR87';  
// $username = 'rahulkumartiwari8750@gmail.com';
// $password = 'MUDR87_SP20904';
// $authKey = 'y5HEnNMsIm14n0HJ';
// $authIV = 'z7BXwJuvCRvA9sj3';




    // // Transaction details
// $payerName = 'Adarsh';
// $payerEmail = 'Test@email.in';
// $payerMobile = '8127343545';
// $payerAddress = 'Patna, Bihar';

    // $clientTxnId = time();
// $amount = 10;
// $amountType = 'INR';
// $mcc = 5137;  
// $channelId = 'W';  
// $callbackUrl = 'https://homeofbulldogs.com/dev/pay-form/wp-callback/wp-callback.php';
// $payerVpa = '6300459407@ybl';
// $byPassFlag = 'true';
// $modeTransfer = 'UPI_APPS_MODE_TRANSFER';
// $seamlessType = 'S2S';

    // $encData = "clientCode=" . $clientCode . "&transUserName=" . $username . "&transUserPassword=" . $password . "&payerName=" . $payerName .
//     "&payerMobile=" . $payerMobile . "&payerEmail=" . $payerEmail . "&payerAddress=" . $payerAddress . "&clientTxnId=" . $clientTxnId .
//     "&amount=" . $amount . "&amountType=" . $amountType . "&mcc=" . $mcc . "&channelId=" . $channelId . "&callbackUrl=" . $callbackUrl .
//     "&browserDetails=English|24-bit|1080|1920|UTC+2" . "&modeTransfer=" . $modeTransfer . "&byPassFlag=" . $byPassFlag ."&payerVpa=".$payerVpa .
//     "&seamlessType=" . $seamlessType;


    // $encryptedData = encrypt($authKey, $authIV, $encData);

    // $base_url = "https://securepay.sabpaisa.in/SabPaisa/sabPaisaInit?v=1";

    // $form_data = [
//     "encData" => $encryptedData,
//     "clientCode" => $clientCode
// ];

    // $headers = [
//     "Content-Type: application/x-www-form-urlencoded"
// ];

    // $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, $base_url);
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form_data));
// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // $response = curl_exec($ch);

    // if (curl_errno($ch)) {
//     echo "cURL Error: " . curl_error($ch);
// } else {
//     echo "Response: " . $response;

    //     $responseData = json_decode($response, true);



    //     // if (isset($responseData['data'])) {
//     //     parse_str($responseData['data'], $parsedData);
//     //     print_r($parsedData);
//     //     // $enc = str_replace(' ', '+', $parsedData['encData']);
//     //     // if (isset($enc)) {
//     //     //     $decryptedResponse = decrypt($authKey,$authIV, $enc);
//     //     //     if ($decryptedResponse) {
//     //     //         echo "Decrypted Response: " . $decryptedResponse;
//     //     //     } else {
//     //     //         echo "Decryption failed.";
//     //     //     }
//     //     // } else {
//     //     //     echo "No encData found in response.";
//     //     // }
//     // } else {
//     //     echo "No encrypted response data found.";
//     // }


    //     if (isset($responseData['data'])) {
//         parse_str($responseData['data'], $parsedData);


    //         $enc = str_replace(' ', '+', $parsedData['encData']);
//         print_r($enc);

    //         $decryptedResponse = decrypt($authKey,$authIV,  $enc);

    //         print_r($decryptedResponse);

    //     } else {
//         echo "No encrypted response data found.";
//     }


    // }

    // curl_close($ch);

    //     }
    private function generateAlphabetOrderId(int $length = 8): string
    {
        $alphabet = 'AaBbCcDdEeFfGghijklmnopqrstHIJuvwxyzKLMNOPQRSTUVWXYZ';      // A–Z
        $maxIndex = strlen($alphabet) - 1;            // 25
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            // cryptographically-secure random pick
            $id .= $alphabet[random_int(0, $maxIndex)];
        }

        return $id;
    }
    private function RudraPayInitiate($amount, $orderid, $mobile)
    {
        $redirectUrl = url('api/pg/upitel/redirect_recharge_successfully/' . $orderid);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://merchant.rudraxpay.com/api/pg/phonepe/initiate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "token": "$2y$10$0Gr4R0w3lsNwag1E7D/dBeQks5igyI.hMbOiyiSErDVq9XG84T9Le",
    "userid": "RXP10100",
    "amount": "' . $amount . '",
    "mobile": "8957287400",
    "name": "Adarsh",
    "orderid": "' . $orderid . '",
    "callback_url": "' . $redirectUrl . '"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }
    // DEPRECATED: Moved to FinQunes Controller
    // private function FinUniqueInitiate($amount, $orderid, $mobile)
    // {
    //     // This function has been moved to App\Http\Controllers\Gateway\FinQunes
    //     // See: app/Http/Controllers/Gateway/FinQunes.php
    // }
    public function phonepe_initiate(Request $r)
    {
        $json_string = json_encode($r->all());
        $response = array('status' => 0, 'title' => "Invalid action!!");
        // return response()->json(["status"=>false,"message"=>"Use payin after 12:00AM IST"]);
        $r->validate([
            'userid' => 'required',
            'token' => 'required',
            'amount' => 'required',
            'mobile' => 'required',
            'callback_url' => 'required',
            'orderid' => 'required',
        ]);
        
        //Validations
        $olduserid = "";

        if (!isset($r->userid) || $r->userid == "")
            return response()->json(["status" => false, "message" => "Userid is required!!"]);
        if (!isset($r->token) || $r->token == "")
            return response()->json(["status" => false, "message" => "Token is required!!"]);
        if (!isset($r->amount) || $r->amount == "")
            return response()->json(["status" => false, "message" => "Amount is required!!"]);
        if (!isset($r->orderid) || $r->orderid == "")
            return response()->json(["status" => false, "message" => "OrderId is required!!"]);
        if (!isset($r->mobile) || $r->mobile == "")
            return response()->json(["status" => false, "message" => "Mobile is required!!"]);
        // if(!isset($r->name) || $r->name == "")
        // return response()->json(["status"=>false,"message"=>"Name is required!!"]);
        
        $UserData = user::where('userid', $r->userid)->first();
        if (!isset($UserData) || !$UserData)
            return response()->json(["status" => false, "message" => "Userid invalid!"]);
            
        if ($UserData->status != 1)
            return response()->json(["status" => false, "message" => "Userid Blocked!"]);
        
        if ($UserData->callback == "")
            return response()->json(["status" => false, "message" => "Service not active!"]);
        
        // ✅ GATEWAY RESTRICTION: EASEBUZZ (28), AUROPAY (29) OR NO GATEWAY (100) ALLOWED
        $allowedGateways = [28, '28', 29, '29', 100, '100'];
        if ($UserData->payingateway != "" && !in_array($UserData->payingateway, $allowedGateways)) {
            return response()->json(["status" => false, "message" => "Gateway not supported. Only Easebuzz (28) and AuroPay (29) are active. Your gateway: " . $UserData->payingateway]);
        }
        // Allow minimum amount of ₹1
        $minAmount = max(1, (float)setting('min_payin')); // Use at least 1, or the setting if higher
        if ($r->amount >= $minAmount) {
            // Use database transaction with proper duplicate checking to prevent race conditions
            try {
                $payment_trn = \DB::transaction(function () use ($r, $UserData, $minAmount) {
                    // Double-check for existing transaction_id with lock to prevent race conditions
                    $exist = \DB::table('payment_requests')
                        ->where('transaction_id', $r->orderid)
                        ->lockForUpdate()
                        ->first();
                    
                    if ($exist) {
                        throw new \Exception("Duplicate entry: Transaction ID already exists");
                    }
                    
                    $charge = ($r->amount / 100) * user('percentage', $r->userid);
                    // if ($r->amount < 350) {
                    //     $charge = setting('sabpaisaBelow350');
                    // }
                    $gst = ($charge / 100) * 18;
                    $trn_id = $r->orderid;
                    
                    $flag = setting('gateway');//1=phonepe,2=upitel,3=ccavenue,4=Cashfree,5=MannualBharatPe,6=Razorpay,7=Sabpaisa,8=Payomatix,9=Runpaisa,10=usdpay

                    if ($UserData->payingateway != "") {
                        $flag = $UserData->payingateway;
                    }
                    if ($flag == 9) {
                        if ($this->isMultipleOf1K($r->amount)) {
                            $r->amount = $r->amount + 1;
                        }
                    }
                    
                    $payment_trn = new Payment_request;
                    $payment_trn->transaction_id = $trn_id;
                    $payment_trn->userid = $r->userid;
                    $payment_trn->amount = $r->amount;
                    $payment_trn->mobile = $r->mobile;
                    $payment_trn->name = $r->name;
                    $payment_trn->tax = $charge + $gst;
                    $payment_trn->data3 = 1;
                    $payment_trn->data6 = $flag;
                    // Set callback URL - use provided callback_url or fallback to user's callback
                    $payment_trn->callbackurl = !empty($r->callback_url) ? $r->callback_url : $UserData->callback;
                    
                    $payment_trn->status = 0;
                    
                    // Final check before save - if somehow a duplicate was created between lock and save
                    $finalCheck = Payment_request::where('transaction_id', $trn_id)->first();
                    if ($finalCheck) {
                        throw new \Exception("Duplicate entry: Transaction ID already exists (race condition detected)");
                    }
                    
                    if (!$payment_trn->save()) {
                        throw new \Exception("Failed to save payment request");
                    }
                    
                    return $payment_trn;
                });
                
                // Payment request created successfully, continue with gateway initiation
                $trn_id = $payment_trn->transaction_id;
                $flag = $payment_trn->data6;
                
                // ✅ ONLY EASEBUZZ (28) & AUROPAY (29) ARE ACTIVE
                
                if ($flag != 28 && $flag != 29 && $flag != 100) {
                    return response()->json([
                        "status" => false, 
                        "message" => "Gateway not supported. Only Easebuzz (28) and AuroPay (29) are active. Your gateway: " . $flag
                    ]);
                }
                
                // ✅ EASEBUZZ (FLAG 28) - ACTIVE GATEWAY
                if ($flag == 28) {
                    // Easebuzz Gateway Integration
                    $easebuzz = new \App\Http\Controllers\Gateway\Easebuzz($this);
                    $res = $easebuzz->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'] ?? $responseData['payment_url'] ?? null;
                            
                            return response()->json([
                                "status" => true, 
                                "message" => "Payment initiated successfully", 
                                "url" => $token_value, 
                                "amount" => $payment_trn->amount, 
                                "tax" => $payment_trn->tax
                            ], 200, [], JSON_UNESCAPED_SLASHES);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                }
                
                // ✅ AUROPAY (FLAG 29) - Payment Link + QR Code Gateway
                if ($flag == 29) {
                    // AuroPay Gateway Integration
                    $auropay = new \App\Http\Controllers\Gateway\AuroPay();
                    $res = $auropay->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'] ?? $responseData['payment_url'] ?? null;
                            
                            $response = [
                                "status" => true, 
                                "message" => "Payment initiated successfully", 
                                "url" => $token_value, 
                                "amount" => $payment_trn->amount, 
                                "tax" => $payment_trn->tax
                            ];
                            
                            // Add QR code to response if available
                            if (isset($responseData['qr_code']) && $responseData['qr_code']) {
                                $response['qr_code'] = $responseData['qr_code'];
                                $response['qr_available'] = true;
                            }
                            
                            // Add short link if available
                            if (isset($responseData['short_link'])) {
                                $response['short_link'] = $responseData['short_link'];
                            }
                            
                            return response()->json($response, 200, [], JSON_UNESCAPED_SLASHES);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                }
                
                // ✅ NO GATEWAY (FLAG 100) - Manual Processing
                if ($flag == 100) {
                    return response()->json([
                        "status" => true, 
                        "message" => "Payment request created. Awaiting manual processing.", 
                        "url" => null, 
                        "amount" => $payment_trn->amount, 
                        "tax" => $payment_trn->tax
                    ]);
                }
                
            } catch (\Exception $e) {
                // Handle duplicate entry or other errors
                $errorMessage = $e->getMessage();
                $isDuplicate = strpos($errorMessage, 'Duplicate') !== false 
                    || strpos($errorMessage, 'already exists') !== false
                    || strpos($errorMessage, 'race condition') !== false
                    || strpos($errorMessage, '1062') !== false; // MySQL duplicate key error code
                
                if ($isDuplicate) {
                    \Log::warning('Duplicate payment request prevented', [
                        'orderid' => $r->orderid,
                        'userid' => $r->userid,
                        'error' => $errorMessage,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    
                    // Return the existing payment request info if available
                    $existing = Payment_request::where('transaction_id', $r->orderid)->first();
                    if ($existing) {
                        return response()->json([
                            "status" => false, 
                            "message" => "Duplicate entry: This order ID already exists",
                            "existing_id" => $existing->id,
                            "existing_status" => $existing->status
                        ]);
                    }
                    
                    return response()->json(["status" => false, "message" => "Duplicate entry: This order ID already exists"]);
                }
                
                \Log::error('Payment request creation failed', [
                    'orderid' => $r->orderid,
                    'userid' => $r->userid,
                    'error' => $errorMessage,
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                return response()->json(["status" => false, "message" => "Failed to create payment request. Please try again."]);
            }
        } else {
            $minAmount = max(1, (float)setting('min_payin')); // Use at least 1, or the setting if higher
            $response = array('status' => false, 'message' => "Amount should be at least ₹" . $minAmount . "!!");
        }
        return response()->json($response);
    }

    public function phonepe_initiate_token($token, $mode)
    {
        // OLD GATEWAY TOKEN HANDLER - NOT NEEDED FOR EASEBUZZ
        return response()->json(["status" => false, "message" => "Legacy gateway not supported"]);
    }
    
    // 🗑️ DELETED: 465 lines of old gateway code removed (flags 1-27)
    // Only Easebuzz (28) remains active in phonepe_initiate() function above

    public function phonepeinitiate_byUser(Request $r)
    {
        // DELETED OLD GATEWAY TOKEN FUNCTION - Easebuzz doesn't use this
        return response()->json(["status" => false, "message" => "Legacy function not supported with Easebuzz"]);
    }

    // 🗑️ DELETED: 485+ lines of old gateway code completely removed (PayIn cases flags 1-27)
    // All old gateway initiation code has been removed - Only Easebuzz (28) remains active

    /* ============================================
       OLD GATEWAY CODE DELETED - FLAGS 1-27
       (PhonePe, Upitel, CCAvenue, Cashfree, etc.)
       ============================================
                } elseif ($flag == 6) {
                    // $razorpay = new Razorpay();
                    $dataa= ($razorpay->RazorpayInitaitePage($trn_id));
                    if(isset($dataa->link)){
                        return response()->json(["status" => true, "message" => "Link successfully generated", "url" => $dataa->link]);
                    }else{
                        return $dataa;
                        return response()->json(["status" => false, "message" => "high traffic"]);
                    }
                    $token_value = ("https://pay.zhninfotech.online/api/payin/razorpay/Initate/" . $trn_id);
                } elseif ($flag == 7) {
                    
                    $Final = $this->SabpaisaInitaiteResponse($trn_id, $r->amount, "User", $r->mobile);
                    if ($Final == "#") {
                        return response()->json(["status" => false, "message" => "High Traffic"]);
                    }
                    if (!$r->expectsJson()) {
                        return redirect('/checkout/' . $r->userid);
                    }
                    return response()->json(["status" => true, "message" => "Link successfully generated", "url" => $Final]);
                } elseif ($flag == 8) {
                    $token_value = url("/api/payin/payomatix/Initate/" . $trn_id);
                } elseif ($flag == 9) {
                    $token_value = url("/api/payin/runpaisa/Initate/" . $trn_id);
                } elseif ($flag == 10) {
                    $postFields = array(
                        'api_key' => 'd702341a1352d96d143c6e236ae3d4ed',
                        'amount' => $r->amount,
                        'mchorderid' => $trn_id,
                        'channel_code' => 102,
                        'callback_url' => "https://merchant.rudraxpay.com/api/payin/usdpay/callback"
                    );
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.usdpay.tech/v3/process_collection.php',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => http_build_query($postFields),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded'
                        ),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    // echo $response;
                    $result = json_decode($response, true);
                    if ($result['status'] == "error") {
                        print_r($result);
                        exit();
                    } else {
                        $redirect_url = $result['payInfo'];
                        $token_value = $redirect_url;
                    }
                } elseif ($flag == 12) {
                    
                    $Final = $this->nsdlInitaiteResponse($trn_id, $r->amount, "User", $r->mobile);
                    // $Final = [];
                    // return $newTxnId = $Final['response'];
                    $newTxnId = $Final['response']['transaction_id'];
                    Payment_request::where('id', $payment_trn->id)->update(['data5' => $newTxnId]);
                    if ($Final == "" || !isset($Final['response']['upi_intent_link']) || $Final['response']['upi_intent_link'] == "") {
                        return response()->json(["status" => false, "message" => "High Traffic"]);
                    } elseif ($Final['response']['upi_intent_link'] != "") {
                        if (!$r->expectsJson()) {
                            return redirect('/checkout/' . $r->userid);
                        }
                        return response()->json(["status" => true, "message" => "Link successfully generated", "url" => $Final['response']['upi_intent_link']]);
                    }
                } elseif ($flag == 13) {
                    $INTENT = true;
                    $res = $this->RudraPayInitiate($r->amount, $trn_id, $UserData->mobile, $UserData->name);
                    if (isset($res->url) && $res->url != "") {
                        $token_value = $res->url;
                        if (!$r->expectsJson()) {
                            return redirect('/checkout/' . $r->userid);
                        }
                        return response()->json(["status" => true, "message" => "Link successfully generated", "url" => $token_value, "amount"=>$payment_trn->amount,"tax"=>$payment_trn->tax]);
                    }
                } elseif ($flag == 14) {
                    // FinQunes Gateway Integration (Flag 14)
                    $finqunes = new \App\Http\Controllers\Gateway\FinQunes($this);
                    $res = $finqunes->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'] ?? $responseData['payment_url'] ?? null;
                            
                            // Always return JSON for API requests (no HTML redirect)
                            return response()->json([
                                "status" => true, 
                                "message" => "Payment initiated successfully", 
                                "url" => $token_value, 
                                "amount" => $payment_trn->amount, 
                                "tax" => $payment_trn->tax
                            ], 200, [], JSON_UNESCAPED_SLASHES);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Xpaisa payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Xpaisa payment initiation failed"]);
                    }
                } elseif ($flag == 15) {
                    $token_value = url("/api/payin/razorpay/Initate/" . $trn_id);
                }elseif ($flag == 16) {
                    $INTENT = true;
                    $res = $this->RudraPayInitiate($r->amount, $trn_id, $UserData->mobile, $UserData->name);
                    if (isset($res->url) && $res->url != "") {
                        $token_value = $res->url;
                        if (!$r->expectsJson()) {
                            return redirect('/checkout/' . $r->userid);
                        }
                        return response()->json(["status" => true, "message" => "Link successfully generated", "url" => $token_value, "amount"=>$payment_trn->amount,"tax"=>$payment_trn->tax]);
                    }
                }elseif ($flag == 18) {
                    // Paydeer Gateway Integration (Flag 18)
                    $paydeer = new \App\Http\Controllers\Gateway\Paydeer();
                    $res = $paydeer->createUpiIntent($r->amount, $trn_id, $r->mobile, $UserData->email ?? 'user@example.com', 'Payment for ' . $trn_id, $UserData->name ?? 'User');
                    
                    if ($res && $res->success) {
                        // Check for different possible response field names
                        $actualPaydeerUrl = null;
                        if (isset($res->data['data']['payment_link'])) {
                            $actualPaydeerUrl = $res->data['data']['payment_link'];
                        } elseif (isset($res->data['payment_link'])) {
                            $actualPaydeerUrl = $res->data['payment_link'];
                        } elseif (isset($res->data['data']['payment_url'])) {
                            $actualPaydeerUrl = $res->data['data']['payment_url'];
                        } elseif (isset($res->data['upi_intent_url'])) {
                            $actualPaydeerUrl = $res->data['upi_intent_url'];
                        } elseif (isset($res->data['payment_url'])) {
                            $actualPaydeerUrl = $res->data['payment_url'];
                        } elseif (isset($res->data['url'])) {
                            $actualPaydeerUrl = $res->data['url'];
                        } elseif (isset($res->data['upi_url'])) {
                            $actualPaydeerUrl = $res->data['upi_url'];
                        } elseif (isset($res->data['intent_url'])) {
                            $actualPaydeerUrl = $res->data['intent_url'];
                        }
                        
                        if ($actualPaydeerUrl) {
                            // Create masked URL using your domain instead of showing Paydeer URL
                            $encodedUrl = base64_encode($actualPaydeerUrl);
                            $token_value = url('/paydeer/redirect/' . $encodedUrl);
                            // Redirect directly to Paydeer URL, not checkout page
                            if (!$r->expectsJson()) {
                                return redirect($token_value);
                            }
                            return response()->json(["status" => true, "message" => "Payment initiated successfully", "url" => $token_value, "amount" => $payment_trn->amount, "tax" => $payment_trn->tax]);
                        } else {
                            return response()->json(["status" => false, "message" => "Xpaisa response received but no payment URL found", "debug" => $res->data]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => isset($res->message) ? $res->message : "Xpaisa payment initiation failed"]);
                    }
                } elseif ($flag == 19) {
                    // HZTPay Gateway Integration (Flag 19)
                    $hztpay = new \App\Http\Controllers\Gateway\HZTPay($this);
                    $res = $hztpay->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'];
                            
                            if (!$r->expectsJson()) {
                                return redirect($token_value);
                            }
                            
                            // Return simple response with only QR code URL
                            return response()->json([
                                "status" => true, 
                                "message" => "Xpaisa payment generated successfully", 
                                "url" => $token_value, 
                                "amount" => $payment_trn->amount, 
                                "tax" => $payment_trn->tax
                            ]);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                } elseif ($flag == 20) {
                    // PayVanta Gateway Integration (Flag 20)
                    $payvanta = new \App\Http\Controllers\Gateway\PayVanta($this);
                    $res = $payvanta->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'];
                            if (!$r->expectsJson()) {
                                return redirect($token_value);
                            }
                            return response()->json(["status" => true, "message" => "Payment initiated successfully", "url" => $token_value, "amount" => $payment_trn->amount, "tax" => $payment_trn->tax]);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Xpaisa payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Xpaisa payment initiation failed"]);
                    }
                } elseif ($flag == 21) {
                    // ASVB Gateway Integration (Flag 21)
                    $asvb = new \App\Http\Controllers\Gateway\ASVB($this);
                    $res = $asvb->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'];
                            if (!$r->expectsJson()) {
                                return redirect($token_value);
                            }
                            return response()->json(["status" => true, "message" => "Xpaisa payment generated successfully", "url" => $token_value, "amount" => $payment_trn->amount, "tax" => $payment_trn->tax]);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                } elseif ($flag == 23) {
                    // PayU Gateway Integration (Flag 23)
                    $payu = new \App\Http\Controllers\Gateway\PayU($this);
                    $res = $payu->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        $statusVal = $responseData['status'] ?? false;
                        $isOk = ($statusVal === true) || (is_string($statusVal) && strtolower($statusVal) === 'success');
                        if ($isOk) {
                            $token_value = $responseData['url'] ?? ($responseData['data']['payment_link'] ?? null);
                            if (!$r->expectsJson()) {
                                return redirect($token_value);
                            }
                            return response()->json(["status" => true, "message" => "Xpaisa payment generated successfully", "url" => $token_value, "amount" => $payment_trn->amount, "tax" => $payment_trn->tax]);
                        } else {
                            return response()->json(["status" => false, "message" => ($responseData['message'] ?? ($responseData['data']['message'] ?? "Payment initiation failed"))]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                } elseif ($flag == 24) {
                    // UnitPayGo Gateway Integration (Flag 24)
                    $unitpaygo = new \App\Http\Controllers\Gateway\UnitPayGo($this);
                    $res = $unitpaygo->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        
                        // UnitPayGo returns raw response with 'statuscode' field (not 'status')
                        $statusCode = $responseData['statuscode'] ?? '';
                        $isOk = ($statusCode === 'TXN' || strtolower($statusCode) === 'success');
                        
                        if ($isOk) {
                            // UnitPayGo returns upi_string and payment_url at top level (not in 'data')
                            $upi_string = $responseData['upi_string'] ?? $responseData['payment_url'] ?? '';
                            
                            // Remove any JSON escape characters from UPI string
                            $upi_string = str_replace('\/', '/', $upi_string);
                            
                            // Save UPI string to payment request for later display
                            $payment_trn->data2 = $upi_string;
                            $payment_trn->save();
                            
                            // Return HTML page URL instead of raw UPI intent
                            $token_value = url('/api/gateway/unitpaygo/pay/' . $trn_id);
                            
                            if (!$r->expectsJson()) {
                                return redirect($token_value);
                            }
                            return response()->json(["status" => true, "message" => "Payment initiated successfully", "url" => $token_value, "amount" => $payment_trn->amount, "tax" => $payment_trn->tax]);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Xpaisa payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Xpaisa payment initiation failed"]);
                    }
                } elseif ($flag == 25) {
                    // Solitpay Gateway Integration (Flag 25)
                    $solitpay = new \App\Http\Controllers\Gateway\Solitpay($this);
                    $res = $solitpay->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'] ?? $responseData['upi_intent'] ?? null;
                            
                            // Always return JSON for API requests (no HTML redirect)
                            return response()->json([
                                "status" => true, 
                                "message" => "Payment initiated successfully", 
                                "url" => $token_value, 
                                "amount" => $payment_trn->amount, 
                                "tax" => $payment_trn->tax
                            ], 200, [], JSON_UNESCAPED_SLASHES);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                } elseif ($flag == 26) {
                    // NSO Gateway Integration (Flag 26)
                    $nso = new \App\Http\Controllers\Gateway\NSO($this);
                    $res = $nso->initiatePayin($r, $trn_id);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'] ?? $responseData['payment_url'] ?? null;
                            
                            // Always return JSON for API requests (no HTML redirect)
                            return response()->json([
                                "status" => true, 
                                "message" => "Xpaisa payment successfully generated", 
                                "url" => $token_value, 
                                "amount" => $payment_trn->amount, 
                                "tax" => $payment_trn->tax
                            ], 200, [], JSON_UNESCAPED_SLASHES);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                } elseif ($flag == 27) {
                    // Spay Gateway Integration (Flag 27)
                    $spay = new \App\Http\Controllers\Gateway\Spay($this);
                    $res = $spay->initiatePayin($r);
                    
                    if ($res && $res->getStatusCode() == 200) {
                        $responseData = json_decode($res->getContent(), true);
                        if ($responseData['status'] === true) {
                            $token_value = $responseData['url'] ?? $responseData['payment_link'] ?? null;
                            
                            // Always return JSON for API requests (no HTML redirect)
                            return response()->json([
                                "status" => true, 
                                "message" => "Xpaisa payment successfully generated", 
                                "url" => $token_value, 
                                "amount" => $payment_trn->amount, 
                                "tax" => $payment_trn->tax
                            ], 200, [], JSON_UNESCAPED_SLASHES);
                        } else {
                            return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payment initiation failed"]);
                        }
                    } else {
                        return response()->json(["status" => false, "message" => "Payment initiation failed"]);
                    }
                }
                ============================================ */

    // Duplicate function removed - phonepe_initiate_token() already defined above at line 1968
    
    /*
    public function phonepe_initiate_token_ORPHAN($token, $mode)
    {
        $encryptedMessage = Crypt::decrypt($token);
        // return $encryptedMessage;
        $url = $encryptedMessage;
        // return $mode;
        if ($mode == 1) {
            $url = "https://mercury-t2.phonepe.com/transact/pg?token=" . $encryptedMessage;
        } elseif ($mode == 2 || $mode == 3 || $mode == 4 || $mode == 6 || $mode == 8 || $mode == 9 || $mode == 26 || $mode == 27) {
            $url = $encryptedMessage;
            if ($url != "#") {
                return redirect($url);
                return header("Location: " . $url);
            }
        } elseif ($mode == 5) {
            $upid = "BHARATPE.8001823795@fbpe";
            $upiholdername = "ADARSH PUSHPENDRA PANDEY";
            $amount = 100;
            $AfterDecodeMes = json_decode($encryptedMessage);
            $url = $AfterDecodeMes[0];
            $trn_id = $AfterDecodeMes[1];
            $dATA = Payment_request::where('transaction_id', $trn_id)->first();
            $amount = $dATA->amount;
            if ($url == "#") {
            } else {
                return view('Gateway.InitiateBharatpe_new', compact('url', 'upiholdername', 'amount', 'dATA', 'upid', 'trn_id'));
                return view('Gateway.InitiateBharatpe', compact('url', 'upiholdername', 'amount', 'dATA'));
                return header("Location: " . $url);
            }
        }
        return view('Gateway.InitiatePhonepe', compact('url'));
    }


    // Duplicate function removed - phonepeinitiate_byUser() already defined at line 1977
    /*
    ORPHAN_DUPLICATE_phonepeinitiate_byUser function body removed
    */

    public function IbrPayoutCheckStatus($trn_id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ibrpay.com/api/PayoutLive.aspx',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ' {
        	"APIID": "API1037",
        	"Token": "29659179-9fc5-46ae-b8f7-f373f39f6cb4",
        	"MethodName": "checkstatus",
        	"OrderID":"' . $trn_id . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
    public function IbrPayout($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ibrpay.com/api/PayoutLive.aspx',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => ' {
        	"APIID": "API1037",
        	"Token": "29659179-9fc5-46ae-b8f7-f373f39f6cb4",
        	"MethodName": "payout",
        	"OrderID":"' . $trn_id . '",
            "Name":"' . $name . '",
            "Amount":"' . $am . '",
            "number":"' . $number . '",
            "ifsc":"' . $ifsc . '",  
            "PaymentType":"IMPS",
            "CustomerMobileNo":"' . $mobile . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
    public function RazorPayPayout($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $am = $am * 100;
        $authToken = "cnpwX2xpdmVfcmwzaVVmNUJsalBIZjc6SG1ndldnVkp4aXVaWkRrdW9keXlCV0ho";
        $curl = curl_init();
        $curl1 = curl_init();
        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/contacts',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
      "name":"' . $name . '",
      "email":"void@merchant.rudraxpay.com",
      "contact":"' . $mobile . '",
      "type":"customer",
      "reference_id":"Mother Solution User"
    }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $authToken
            ),
        ));
        $response1 = curl_exec($curl1);
        curl_close($curl1);
        $contDataId = json_decode($response1)->id;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/fund_accounts',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
      "account_type":"bank_account",
      "contact_id":"' . $contDataId . '",
      "bank_account":{
        "name":"' . $name . '",
        "ifsc":"' . $ifsc . '",
        "account_number":"' . $number . '"
      }
    }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $authToken
            ),
        ));
        // CURLOPT_POSTFIELDS =>'{
        //   "account_type":"vpa",
        //   "contact_id":"'.$contDataId.'",
        //   "vpa":{
        //     "address":"'.$upiid.'"
        //   }
        $response = curl_exec($curl);

        curl_close($curl);
        if (isset(json_decode($response)->id)) {
            $funcId = json_decode($response)->id;
        } else {
            return json_decode($response, true);
        }
        if ($response) {
            $data = json_decode($response);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.razorpay.com/v1/payouts',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
          "account_number": "409002275025",
          "fund_account_id": "' . $funcId . '",
          "amount": ' . $am . ',
          "currency": "INR",
          "mode": "IMPS",
          "purpose": "refund",
          "queue_if_low_balance": false,
          "reference_id": "' . $trn_id . '",
          "narration": "Money Transfer to our workers"
        }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Basic ' . $authToken
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response, true);
        }
    }
    public function RazorPayPayoutUPI($am, $trn_id, $mobile, $name, $number)
    {
        $am = $am * 100;
        $authToken = "cnpwX2xpdmVfcmwzaVVmNUJsalBIZjc6SG1ndldnVkp4aXVaWkRrdW9keXlCV0ho";
        $curl = curl_init();
        $curl1 = curl_init();
        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/contacts',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
      "name":"' . $name . '",
      "email":"void@merchant.rudraxpay.com",
      "contact":"' . $mobile . '",
      "type":"customer",
      "reference_id":"Mother Solution User"
    }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $authToken
            ),
        ));
        $response1 = curl_exec($curl1);
        curl_close($curl1);
        $contDataId = json_decode($response1)->id;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/fund_accounts',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
      "account_type":"vpa",
      "contact_id":"' . $contDataId . '",
      "vpa":{
        "address":"' . $number . '"
      }
    }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $authToken
            ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        if (isset(json_decode($response)->id)) {
            $funcId = json_decode($response)->id;
        } else {
            return json_decode($response, true);
        }
        if ($response) {
            $data = json_decode($response);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.razorpay.com/v1/payouts',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
          "account_number": "409002275025",
          "fund_account_id": "' . $funcId . '",
          "amount": ' . $am . ',
          "currency": "INR",
          "mode": "UPI",
          "purpose": "refund",
          "queue_if_low_balance": false,
          "reference_id": "' . $trn_id . '",
          "narration": "Money Transfer to our workers"
        }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Basic ' . $authToken
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return json_decode($response, true);
        }
    }
    private function WaoPayPayoutUPI()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.wowpe.in/api/api/api-module/payout/payout',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "clientId":"9f73d64a-e8a1-4394-9588-840c2d4a7c5c",
    "secretKey":"20d68aac-0c25-4528-8bfe-c3f2692e0e8b",
    "number":"8957287400",
    "amount":"10",
    "transferMode":"IMPS",
    "accountNo":"50200086802686",
    "ifscCode":"HDFC0005252",
    "beneficiaryName":"Adarsh",
    "vpa":"motherpay@axl",
    "clientOrderId":"PHONEPETCGFHG5486548684596598"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);

    }
    private function WaoPayPayoutIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.wowpe.in/api/api/api-module/payout/payout',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "clientId":"9f73d64a-e8a1-4394-9588-840c2d4a7c5c",
            "secretKey":"20d68aac-0c25-4528-8bfe-c3f2692e0e8b",
            "number":"' . $mobile . '",
            "amount":"' . $am . '",
            "transferMode":"IMPS",
            "accountNo":"' . $number . '",
            "ifscCode":"' . $ifsc . '",
            "beneficiaryName":"' . $name . '",
            "vpa":"motherpay@axl",
            "clientOrderId":"' . $trn_id . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    private function calculate_hmac($secret, $timestamp, $body, $path, $query_string = '', $method = 'GET')
    {
        $message = $method . "\n" . $path . "\n" . $query_string . "\n" . $body . "\n" . $timestamp . "\n";
        return hash_hmac('sha512', $message, $secret);
    }
    private function ValojPayoutIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $key = "cf537502e5db4b61d7b0ba66aa5351f1";
        $secret = "51eac8df54f2a8510dc9a0b4523543ea";
        $payid = "212";
        $salt = "6c6cdf333411949c3b33b5b719845909";
        $x_timestamp = floor(microtime(true) * 1000);
        $body = '{"address": "NOIDA SECTOR-12", "payment_type": 3, "amount":' . $am . ', "email": "abc@gmail.com", "mobile_number": "9999999999", "account_number": "' . $number . '", "ifsc_code": "' . $ifsc . '", "merchant_order_id":"' . $trn_id . '","name":"Mia Kumar"}';
        $baseURL = "https://apis.velozpay.com";
        $path = "/api/v1/payout/process";
        $signature = $this->calculate_hmac($secret, $x_timestamp, $body, $path, "", "POST");
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $baseURL . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array(
                'X-Timestamp: ' . $x_timestamp,
                'access_key: ' . $key,
                'signature: ' . $signature,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    private function MotherpayIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://merchant.rudraxpay.com/api/payout/initiate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "token": "$2y$10$0Gr4R0w3lsNwag1E7D/dBeQks5igyI.hMbOiyiSErDVq9XG84T9Le",
    "userid": "RXP10100",
    "amount": "' . $am . '",
    "mobile": "' . $mobile . '",
    "name":"' . $name . '",
    "number":"' . $number . '",
    "ifsc":"' . $ifsc . '",
    "orderid": "' . $trn_id . '"
}',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    private function USDPAYPayoutIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $curl = curl_init();
        $data = array(
            'api' => 'd702341a1352d96d143c6e236ae3d4ed',
            'bank_account' => $number,
            'amount' => $am,
            'ifsc' => $ifsc,
            'bank_name' => 'BOI',
            'holder_name' => $name,
            'callback_url' => url('api/payout/usdpay/callback')
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://usdpay.tech/api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    public function SafexPayPayoutStatusApi($trn_id)
    {
        $curl = curl_init();
        $data = array(
            'transferId' => $trn_id,
            'uniqpayId' => "UPC1745054536460712V"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apigateway.myuniqpay.com/payout/paymentinquiry/api/v1/payments/get-status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Upay-Client-Id: 4c2c8290-fb9b-4e14-baba-8f29f8bfd86e',
                'X-Upay-Client-Secret: a401f59a-595a-4867-a238-dc92cf09744a',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    public function UNIQPAYPayoutStatusApi($trn_id)
    {
        $curl = curl_init();
        $data = array(
            'transferId' => $trn_id,
            'uniqpayId' => "UPC1745054536460712V"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apigateway.myuniqpay.com/payout/paymentinquiry/api/v1/payments/get-status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Upay-Client-Id: 4c2c8290-fb9b-4e14-baba-8f29f8bfd86e',
                'X-Upay-Client-Secret: a401f59a-595a-4867-a238-dc92cf09744a',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    private function UNIQPAYPayoutIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $curl = curl_init();
        $data = array(
            'email' => 'info@rudraxpay.in',
            'bankAccount' => $number,
            'amount' => $am,
            'ifsc' => $ifsc,
            'phone' => $mobile,
            'transferMode' => 'IMPS',
            'name' => $name,
            'transferId' => $trn_id,
            'address' => "Sector 62 noida",
            'uniqpayId' => "UPC1745054536460712V"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apigateway.myuniqpay.com/payout-switch/api/v1/payments/payouts',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Upay-Client-Id: 4c2c8290-fb9b-4e14-baba-8f29f8bfd86e',
                'X-Upay-Client-Secret: a401f59a-595a-4867-a238-dc92cf09744a',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    private function BENAKPayPayoutIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    {
        $curl = curl_init();
        $data = array(
            'name' => $name,
            'email' => 'info@rudraxpay.in',
            'phone' => $mobile,
            'bankAccount' => $number,
            'amount' => $am,
            'ifsc' => $ifsc,
            'transferMode' => 'IMPS',
            'transferId' => $trn_id,
            'address' => "Sector 62 noida",
            'clientId' => "BPC9dc2310040"
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.benakpay.com/client/ms-payment-imps',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Client-Id: b2a51d20-238a-47d1-9225-a5dc92187449',
                'Client-SecretId: 39b1137a-b471-42d9-a741-fed049e9e550',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
    // DEPRECATED: Moved to FinQunes Controller
    // private function FINUNIQUEPayoutIMPS($am, $trn_id, $mobile, $name, $number, $ifsc)
    // {
    //     // This function has been moved to App\Http\Controllers\Gateway\FinQunes
    //     // See: app/Http/Controllers/Gateway/FinQunes.php::initiatePayout()
    // }
    //     private function UNIQPAYPayoutIMPS($am,$trn_id,$mobile,$name,$number,$ifsc){
//         $curl = curl_init();

    // curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://apigateway.myuniqpay.com/payout-switch/api/v1/payments/payouts',
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'POST',
//   CURLOPT_POSTFIELDS =>'{
//     "email": "info@rudraxpay.in",
//     "bankAccount": "923020059858241",
//     "amount": "100",
//     "ifsc": "UTIB0001691",
//     "phone": "7060471592",
//     "transferMode": "IMPS",
//     "name": "Mia",
//     "transferId": "'.$trn_id.'",
//     "address": "Sector 62 noida",
//     "uniqpayId": "UPC1745054536460712V"
// }',
//   CURLOPT_HTTPHEADER => array(
//     'X-Upay-Client-Id: 4c2c8290-fb9b-4e14-baba-8f29f8bfd86e',
//     'X-Upay-Client-Secret: a401f59a-595a-4867-a238-dc92cf09744a',
//     'Content-Type: application/json'
//   ),
// ));

    // $response = curl_exec($curl);

    // curl_close($curl);
//         return json_decode($response);
//     }
    public function PayoutInitiateByApiUPI(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        //Validations
        if (!isset($r->userid) || $r->userid == "")
            return response()->json(["status" => false, "message" => "Userid is required!!"]);
        if (!isset($r->token) || $r->token == "")
            return response()->json(["status" => false, "message" => "Token is required!!"]);
        if (!isset($r->amount) || $r->amount == "")
            return response()->json(["status" => false, "message" => "Amount is required!!"]);
        if (!isset($r->orderid) || $r->orderid == "")
            return response()->json(["status" => false, "message" => "OrderId is required!!"]);
        if (!isset($r->upi) || $r->upi == "")
            return response()->json(["status" => false, "message" => "UPI Id is required!!"]);
        if (!isset($r->name) || $r->name == "")
            return response()->json(["status" => false, "message" => "Name is required!!"]);
        if (!isset($r->mobile) || $r->mobile == "")
            return response()->json(["status" => false, "message" => "Mobile is required!!"]);
        $UserData = user::where('userid', $r->userid)->where('token', $r->token)->where('status', 1)->first();
        if (!$UserData)
            return response()->json(["status" => false, "message" => "User not Found!!"]);
        $UserWallet = Wallet::where('userid', $UserData->userid)->first();
        if (!$UserWallet) {
            $UserWallet = new Wallet;
            $UserWallet->userid = $UserData->userid;
            $UserWallet->save();
        }
        if ($r->amount < 10)
            $response = array('status' => false, 'message' => "Amount should be more than 10!!");
        $exist = PayoutRequest::where('transaction_id', $r->orderid)->where('status', 1)->first();
        if ($exist) {
            return response()->json(["status" => false, "message" => "Dublicate entry!!"]);
        }
        if ($UserData->out_ip != $r->ip())
            return response()->json(array('status' => false, 'message' => "IP Restricted", "your_ip" => $r->ip()));
        if ($UserData->out_callback == "")
            return response()->json(["status" => false, "message" => "Service not active!"]);
        // $charge = ($r->amount/100)*user('payout_percentage',$r->userid);
        if ($r->amount > 999) {
            $charge = ($r->amount / 100) * $UserData->out_percentage;
        } else {
            $charge = 5.8;
        }
        $gst = ($charge / 100) * 18;
        $FinalAmount = $r->amount + $charge + $gst;
        if (!$UserWallet || $UserWallet->payout < $FinalAmount) {
            return response()->json(["status" => false, "message" => "Insufficient Fund!!"]);
        }
        $trn_id = $r->orderid;
        $payment_trn = new PayoutRequest;
        $payment_trn->transaction_id = $trn_id;
        $payment_trn->userid = $r->userid;
        $payment_trn->amount = $r->amount;
        $payment_trn->tax = $charge + $gst;
        $payment_trn->byApi = 1;
        $payment_trn->status = 0;
        if ($payment_trn->save()) {
            $flag = 1;//1=WaoPay
            if ($flag == 1) {
                // $res = $this->IbrPayout($r->amount,$trn_id,$r->mobile,$r->name,$r->number,$r->ifsc);
                $res = $this->WaoPayPayoutUPI($r->amount, $trn_id, $r->mobile, $r->name, $r->upi);
                return $res;
                if ($res['error']['code'] == "NA") {
                    if (isset($res['status']) && ($res['status'] == 'processing' || $res['status'] == 'queued')) {
                        $PayoutId = $res['id'];
                        $Final_amount = $UserWallet->payout - $FinalAmount;
                        Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                        return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                    } else {
                        PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                        return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                    }
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res['error']['description'], "id" => $trn_id]);
                }
            }
            return response()->json(["status" => false, "message" => "Something wents wrong"]);
        }
        return response()->json($response);
    }
    public function PayoutInitiateByUser(Request $r)
    {
        if ($this->PayoutClose) {
            $response = array('status' => 0, 'title' => "settlement pending!!");
            return response()->json($response);
        }
        $response = array('status' => 0, 'title' => "Invalid action!!");
        //Validations
        if (!isset($r->userid) || $r->userid == "")
            return response()->json(["status" => false, "message" => "Userid is required!!"]);
        if (!isset($r->token) || $r->token == "")
            return response()->json(["status" => false, "message" => "Token is required!!"]);
        if (!isset($r->amount) || $r->amount == "")
            return response()->json(["status" => false, "message" => "Amount is required!!"]);
        if (!isset($r->orderid) || $r->orderid == "")
            return response()->json(["status" => false, "message" => "OrderId is required!!"]);
        if (!isset($r->ifsc) || $r->ifsc == "")
            return response()->json(["status" => false, "message" => "IFSC is required!!"]);
        if (!isset($r->number) || $r->number == "")
            return response()->json(["status" => false, "message" => "Account Number is required!!"]);
        if (!isset($r->name) || $r->name == "")
            return response()->json(["status" => false, "message" => "Name is required!!"]);
        if (!isset($r->mobile) || $r->mobile == "")
            return response()->json(["status" => false, "message" => "Mobile is required!!"]);
        $UserData = user::where('userid', $r->userid)->where('token', $r->token)->where('status', 1)->first();
        if (!$UserData)
            return response()->json(["status" => false, "message" => "User not found or blocked or invalid token!!"]);

        $UserWallet = Wallet::where('userid', $UserData->userid)->first();
        if (!$UserWallet) {
            $UserWallet = new Wallet;
            $UserWallet->userid = $UserData->userid;
            $UserWallet->save();
        }
        if ($r->amount < setting('min_mannual_payout'))
            $response = array('status' => false, 'message' => "Amount should be more than " . setting('min_mannual_payout') . "!!");
        $exist = PayoutRequest::where('transaction_id', $r->orderid)->where('status', 1)->first();
        if ($exist) {
            return response()->json(["status" => false, "message" => "Dublicate entry!!"]);
        }

        // $charge = ($r->amount/100)*user('payout_percentage',$r->userid);
        if ($r->amount > 999) {
            $charge = ($r->amount / 100) * $UserData->out_percentage;
        } else {
            $charge = setting('payoutflatcharge');
        }
        $gst = ($charge / 100) * 18;
        $FinalAmount = $r->amount;
        if ($r->userid != "UR10374") {
            $FinalAmount = $r->amount + $gst + $charge;
        }
        if (!$UserWallet || $UserWallet->payout < $FinalAmount) {
            return response()->json(["status" => false, "message" => "Insufficient Fund!!"]);
        }
        $trn_id = $r->orderid;
        $payment_trn = new PayoutRequest;
        $payment_trn->transaction_id = $trn_id;
        $PayoutId = "RDXPAY_" . date("HisDmy") . rand(111, 999);
        $payment_trn->txnid2 = $PayoutId;
        $payment_trn->userid = $r->userid;
        $payment_trn->amount = $r->amount;
        $payment_trn->payout_id = $r->amount;
        $payment_trn->tax = $charge + $gst;
        $payment_trn->holder_name = $r->name;
        $payment_trn->account_no = $r->number;
        $payment_trn->ifsc_code = $r->ifsc;
        $payment_trn->mode = "IMPS";
        $payment_trn->byApi = 0;
        $payment_trn->ip = $r->ip();
        $payment_trn->status = 0;
        if ($payment_trn->save()) {
            $flag = $UserData->payoutgateway;//1=IBR,2=Razorpay,3=WaoPay,4=Universalpay,5=usdpay,6=kavachpay,7=Safexpay,8=uniqpay

            // $res = $this->RazorPayPayout($r->amount,$trn_id,$r->mobile,$r->name,$r->number,$r->ifsc);
            //     if($res['error']['code'] == "NA"){
            //     if(isset($res['status']) && ($res['status'] == 'processing' || $res['status'] == 'queued')){
            //         $PayoutId = $res['id'];
            $Final_amount = $UserWallet->payout - $FinalAmount;
            Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
            PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
            return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
            // }else{
            //     PayoutRequest::where('id',$payment_trn->id)->update(["status"=>2]);
            //     return response()->json(["status"=>false,"message"=>"Error","id"=>$PayoutId]);
            // }
            // }else{
            //     PayoutRequest::where('id',$payment_trn->id)->update(["status"=>2]);
            //     return response()->json(["status"=>false,"message"=>$res['error']['description'],"id"=>$trn_id]);
            // }
            if ($flag == 1) {
                $res = $this->IbrPayout($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                if (!$res) {
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $trn_id]);
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res['status'] == "failed") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    if ($res['mess'] == "Low Balance") {
                        return response()->json(["status" => false, "message" => "Technically issue, Contact support", "id" => $trn_id]);
                    }
                    if ($res['mess'] == "insufficient balance in account") {
                        return response()->json(["status" => false, "message" => "Bank server issues. (Code:ISBIAC)", "id" => $trn_id]);
                    }
                    return response()->json(["status" => false, "message" => $res['mess'], "id" => $trn_id, "dataByBank" => $res]);
                }
                if (isset($res['status']) && ($res['status'] == 'success')) {
                    $PayoutId = $res['data']['STID'];
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 2) {
                $res = $this->RazorPayPayout($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                if ($res['error']['code'] == "NA") {
                    if (isset($res['status']) && ($res['status'] == 'processing' || $res['status'] == 'queued')) {
                        $PayoutId = $res['id'];
                        $Final_amount = $UserWallet->payout - $FinalAmount;
                        Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                        return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                    } else {
                        PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                        return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                    }
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res['error']['description'], "id" => $trn_id]);
                }
            } elseif ($flag == 3) {
                $res = $this->WaoPayPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->statusCode != 1) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    if ($res->message == "Low Balance") {
                        return response()->json(["status" => false, "message" => "Technically issue, Contact support", "id" => $trn_id]);
                    }
                    if ($res->message == "insufficient balance in account") {
                        return response()->json(["status" => false, "message" => "Bank server issues. (Code:ISBIAC)", "id" => $trn_id]);
                    }
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id, "dataByBank" => $res]);
                }
                if (isset($res->statusCode) && ($res->statusCode == 1)) {
                    $PayoutId = $res->clientOrderId;
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 4) {
                $res = $this->PayinFunction->UniversePayPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return  $res;
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if (!$res->status) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id]);
                }
                if (isset($res->status) && ($res->status)) {
                    $PayoutId = $res->data->data->orderId;
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 5) {
                $res = $this->USDPAYPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return  $res;
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->status != "success") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id]);
                }
                if (isset($res->status) && ($res->status == "success")) {
                    $PayoutId = "";
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 6) {
                $res = $this->PayinFunction->CashKavachPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return  $res;
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->statusCode == "ERR") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id]);
                }
                if (isset($res->statusCode) && ($res->statusCode == "TUP")) {
                    $PayoutId = "";
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }

            } elseif ($flag == 7) {
                $res = $this->PayinFunction->SafexpayPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                return $res;
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->status != "success") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id]);
                }
                if (isset($res->status) && ($res->status == "success")) {
                    $PayoutId = "";
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            }
            return response()->json(["status" => false, "message" => "Something wents wrong"]);
        }
        return response()->json($response);
    }
    public function PayoutInitiateByApi(Request $r)
    {
        // if($this->PayoutClose){
        // $response = array('status' => 0, 'title' => "settlement pending!!");
        // return response()->json($response);
        // }
        $response = array('status' => 0, 'title' => "Invalid action!!");
        //Validations
        if (!isset($r->userid) || $r->userid == "")
            return response()->json(["status" => false, "message" => "Userid is required!!"]);
        if (!isset($r->token) || $r->token == "")
            return response()->json(["status" => false, "message" => "Token is required!!"]);
        if (!isset($r->amount) || $r->amount == "")
            return response()->json(["status" => false, "message" => "Amount is required!!"]);
        if (!isset($r->orderid) || $r->orderid == "")
            return response()->json(["status" => false, "message" => "OrderId is required!!"]);
        if (!isset($r->ifsc) || $r->ifsc == "")
            return response()->json(["status" => false, "message" => "IFSC is required!!"]);
        if (!isset($r->number) || $r->number == "")
            return response()->json(["status" => false, "message" => "Account Number is required!!"]);
        if (!isset($r->name) || $r->name == "")
            return response()->json(["status" => false, "message" => "Name is required!!"]);
        if (!isset($r->mobile) || $r->mobile == "")
            return response()->json(["status" => false, "message" => "Mobile is required!!"]);
        $UserData = user::where('userid', $r->userid)->where('token', $r->token)->where('status', 1)->first();
        if (!$UserData)
            return response()->json(["status" => false, "message" => "User not found or blocked or invalid token!!"]);
        
        // ✅ GATEWAY RESTRICTION: ONLY EASEBUZZ (28) ALLOWED FOR PAYOUT
        $allowedPayoutGateways = [28, '28', 100, '100'];
        if ($UserData->payoutgateway != "" && !in_array($UserData->payoutgateway, $allowedPayoutGateways)) {
            return response()->json(["status" => false, "message" => "Payout gateway not supported. Only Easebuzz (ID: 28) is active. Your gateway: " . $UserData->payoutgateway]);
        }

        $UserWallet = Wallet::where('userid', $UserData->userid)->first();
        if (!$UserWallet) {
            $UserWallet = new Wallet;
            $UserWallet->userid = $UserData->userid;
            $UserWallet->save();
        }
        if ($r->amount < setting('min_payout'))
            $response = array('status' => false, 'message' => "Amount should be more than " . setting('min_payout') . "!!");
        $exist = PayoutRequest::where('transaction_id', $r->orderid)->where('status', 1)->first();
        if ($exist) {
            return response()->json(["status" => false, "message" => "Dublicate entry!!"]);
        }
        // if($r->ipShare != "hjbfjbheILOVErhbjeYOUrhbfPURNIMArhjbghjb"){
        //     if ($UserData->out_ip != $r->ip())
        //     return response()->json(array('status' => false, 'message' => "IP Restricted","your_ip"=>$r->ip()));
        // }
        if ($UserData->out_callback == "")
            return response()->json(["status" => false, "message" => "Service not active!"]);

        // $charge = ($r->amount/100)*user('payout_percentage',$r->userid);
        if ($r->amount > 600) {
            $charge = ($r->amount / 100) * $UserData->out_percentage;
        } else {
            $charge = setting('payoutflatcharge');
        }
        $gst = ($charge / 100) * 18;
        $FinalAmount = $r->amount + $charge + $gst;
        $mainWallet = $UserWallet->payout;
        // if($r->userid == "UR10362"){
        //     $mainWallet = $UserWallet->payout-36762.668;
        // }
        if (!$UserWallet || $mainWallet < $FinalAmount) {
            return response()->json(["status" => false, "message" => "Insufficient Fund!!"]);
        }
        $trn_id = $r->orderid;
        $PayoutId = "RDXPAY_" . date("HisDmy") . rand(111, 999);
        $payment_trn = new PayoutRequest;
        $payment_trn->transaction_id = $trn_id;
        $payment_trn->txnid2 = $PayoutId;
        $payment_trn->userid = $r->userid;
        $payment_trn->amount = $r->amount;
        $payment_trn->tax = $charge + $gst;
        $payment_trn->holder_name = $r->name;
        $payment_trn->account_no = $r->number;
        $payment_trn->ifsc_code = $r->ifsc;
        $payment_trn->mode = "IMPS";
        $payment_trn->byApi = 1;
        $payment_trn->ip = $r->ip();
        $payment_trn->status = 0;
        // return "BANK SERVER ISSUE";
        if ($payment_trn->save()) {
            $flag = $UserData->payoutgateway;
            
            // ✅ ONLY EASEBUZZ (FLAG 28) SUPPORTED - All other gateway cases DELETED
            
            if ($flag != 28 && $flag != 100) {
                PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                return response()->json([
                    "status" => false, 
                    "message" => "Payout gateway not supported. Only Easebuzz (ID: 28) is active. Your gateway: " . $flag
                ]);
            }
            
            // ✅ EASEBUZZ PAYOUT (FLAG 28)
            if ($flag == 28) {
                // Easebuzz Payout Integration
                $easebuzz = new \App\Http\Controllers\Gateway\Easebuzz($this);
                $res = $easebuzz->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['payout_id'] ?? $responseData['transaction_id'] ?? $trn_id;
                        // Debit wallet
                $Final_amount = $UserWallet->payout - $FinalAmount;
                Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout initiated successfully", "id" => $payoutId]);
                    } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "Easebuzz payout initiation failed"]);
                    }
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Easebuzz payout initiation failed"]);
                }
            }
            
            // 🗑️ DELETED: All old payout gateway cases (flags 1-27) - 570+ lines removed
        }
        return response()->json($response);
    }
    
    // 🗑️ DELETED: Old payout functions (IbrPayout, RazorPayPayout, WaoPayout, etc.) and all gateway switch cases
    // 570+ lines of payout gateway code removed - Only Easebuzz (28) remains active above
    
    /* ORPHANED CODE FROM HERE TO PayoutStatusCron - WILL BE CLEANED
                }
                if ($res['status'] == "failed") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    if ($res['mess'] == "Low Balance") {
                        return response()->json(["status" => false, "message" => "Technically issue, Contact support", "id" => $trn_id]);
                    }
                    if ($res['mess'] == "insufficient balance in account") {
                        return response()->json(["status" => false, "message" => "Bank server issues. (Code:ISBIAC)", "id" => $trn_id]);
                    }
                    return response()->json(["status" => false, "message" => $res['mess'], "id" => $trn_id, "dataByBank" => $res]);
                }
                if (isset($res['status']) && ($res['status'] == 'success')) {
                    $PayoutId = $res['data']['STID'];
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 2) {
                $res = $this->RazorPayPayout($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                if ($res['error']['code'] == "NA") {
                    if (isset($res['status']) && ($res['status'] == 'processing' || $res['status'] == 'queued')) {
                        $PayoutId = $res['id'];
                        $Final_amount = $UserWallet->payout - $FinalAmount;
                        Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                        return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                    } else {
                        PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                        return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                    }
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res['error']['description'], "id" => $trn_id]);
                }
            } elseif ($flag == 3) {
                $res = $this->WaoPayPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->statusCode != 1) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    if ($res->message == "Low Balance") {
                        return response()->json(["status" => false, "message" => "Technically issue, Contact support", "id" => $trn_id]);
                    }
                    if ($res->message == "insufficient balance in account") {
                        return response()->json(["status" => false, "message" => "Bank server issues. (Code:ISBIAC)", "id" => $trn_id]);
                    }
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id, "dataByBank" => $res]);
                }
                if (isset($res->statusCode) && ($res->statusCode == 1)) {
                    $PayoutId = $res->clientOrderId;
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 4) {
                $res = $this->PayinFunction->UniversePayPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return  $res;
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if (!$res->status) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id]);
                }
                if (isset($res->status) && ($res->status)) {
                    $PayoutId = $res->data->data->orderId;
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 5) {
                $res = $this->USDPAYPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return  $res;
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->status != "success") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id]);
                }
                if (isset($res->status) && ($res->status == "success")) {
                    $PayoutId = "";
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 6) {
                $res = $this->PayinFunction->CashKavachPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return  $res;
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->statusCode == "ERR") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $trn_id]);
                }
                if (isset($res->statusCode) && ($res->statusCode == "TUP")) {
                    $PayoutId = "";
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => $res]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }

            } elseif ($flag == 7) {
                $res = $this->PayinFunction->SafexpayPayoutIMPS($r->amount, $PayoutId, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if (isset($res->errorMessage)) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Parameter/Query Issue", "id" => $trn_id]);
                }
                $res = json_decode($res);
                $resstatus = $res->payOutBean->bankStatus;
                // return  $resstatus;
                //REJECTED
                $PayoutId = "";
                if (isset($resstatus) && ($resstatus == "PENDING")) {
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId, "dataByBank" => true]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Error", "id" => $PayoutId]);
                }
            } elseif ($flag == 8) {
                // return $flag;
                $res = $this->UNIQPAYPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return ($res);
                if (!$res || !isset($res->response->statusCode) || $res->response->statusCode == "TXNF") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                // if($res->response->statusCode != "success"){
                //     PayoutRequest::where('id',$payment_trn->id)->update(["status"=>2]);
                //     return response()->json(["status"=>false,"message"=>$res->message,"id"=>$trn_id]);
                // }
                $log = new Logs;
                $log->uniqueid = "UNIQPAYRequest";
                $log->value = json_encode($res);
                $log->data1 = $trn_id;
                $log->save();
                $PayoutId = "";
                if (isset($res->statusCode)) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => $res->message, "id" => $PayoutId]);
                } elseif (isset($res->response->statusCode) && ($res->response->statusCode == "TXNS" || $res->response->statusCode == "TXNP" || $res->response->statusCode == "TXNA" || $res->response->statusCode == "TXNR")) {
                    // $Final_amount = $UserWallet->payout-$FinalAmount;
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->decrement('payout', $FinalAmount);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                } elseif (isset($res->response->statusCode) && $res->response->statusCode == "TXNF") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Failed", "id" => $PayoutId]);
                } else {
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $FinalAmount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                }
            } elseif ($flag == 9) {
                // return $flag;
                $res = $this->ValojPayoutIMPS($r->amount * 100, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // return ($res);
                // return ($res);
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->success == false) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Some request issue", "id" => $trn_id]);
                }
                $PayoutId = "";
                if (isset($res->data->status) && ($res->data->status == "Pending")) {
                    Wallet::where('userid', $UserData->userid)->decrement('payout', $FinalAmount);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                } else {
                    Wallet::where('userid', $UserData->userid)->increment("payout", $FinalAmount);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                }
            } elseif ($flag == 10) {
                // return $flag;
                $res = $this->MotherpayIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // return ($res);
                // return ($res);
                if (!$res) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                if ($res->status == false) {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Some request issue", "id" => $trn_id, "res" => $res]);
                }
                $PayoutId = "";
                if (isset($res->status) && ($res->status == true)) {
                    Wallet::where('userid', $UserData->userid)->decrement('payout', $FinalAmount);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                } else {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Failed", "id" => $PayoutId]);
                }
            }elseif ($flag == 11) {
                // return $flag;
                $res = $this->BENAKPayPayoutIMPS($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc);
                // echo json_encode($res);
                // return ($res);
                if (!$res || !isset($res->statusCode) || $res->statusCode == "TXNF") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                }
                // if($res->response->statusCode != "success"){
                //     PayoutRequest::where('id',$payment_trn->id)->update(["status"=>2]);
                //     return response()->json(["status"=>false,"message"=>$res->message,"id"=>$trn_id]);
                // }
                // $log = new Logs;
                // $log->uniqueid = "UNIQPAYRequest";
                // $log->value = json_encode($res);
                // $log->data1 = $trn_id;
                // $log->save();
                $PayoutId = "";
                if (isset($res->statusCode) && ($res->statusCode == "TXNS" || $res->statusCode == "TXNP" || $res->statusCode == "TXNA" || $res->statusCode == "TXNR")) {
                    // $Final_amount = $UserWallet->payout-$FinalAmount;
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->decrement('payout', $FinalAmount);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                } elseif (isset($res->statusCode) && $res->statusCode == "TXNF") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Failed", "id" => $PayoutId]);
                } else {
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->update(["payout" => $FinalAmount]);
                    // PayoutRequest::where('id',$payment_trn->id)->update(["payout_id"=>$PayoutId]);
                    return response()->json(["status" => true, "message" => "Success", "id" => $PayoutId]);
                }
            }elseif ($flag == 12) {
                // FinQunes Payout (Flag 12)
                $finqunes = new \App\Http\Controllers\Gateway\FinQunes($this);
                $res = $finqunes->initiatePayout($r, $trn_id, $r->amount, $r->mobile, $r->name, $r->number, $r->ifsc);
                
                if (!$res || !isset($res->status) || $res->status != "processed") {
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json([
                        "status" => false, 
                        "message" => isset($res->message) ? $res->message : "Bank server issue", 
                        "id" => $trn_id,
                        "res" => $res
                    ]);
                }
                
                if (isset($res->status) && ($res->status == "processed")) {
                    $Final_amount = $UserWallet->payout - $FinalAmount;
                    Wallet::where('userid', $UserData->userid)->decrement('payout', $FinalAmount);
                    return response()->json([
                        "status" => true, 
                        "message" => "Xpaisa payout initiated successfully", 
                        "id" => $trn_id
                    ]);
                }
            } elseif ($flag == 13) {
                // ===== BingePay Payout (UPI/IMPS/NEFT/RTGS) =====
                try {
                    $url = 'https://bingepay.co.in/UpiGateway/Payout/Gateway';
                    $params = [
                        'Token'     => 'eZD/IpFGChstCPhUCyqq',      // hardcoded as requested
                        'Amount'    => (string) $r->amount,
                        'type'      => $payment_trn->mode ?? 'IMPS',  // you set "IMPS" above; keep same
                        'AccName'   => $r->name,
                        'AccNo'     => $r->number,
                        'IFSC'      => $r->ifsc,
                        'RequestID' => $trn_id,                       // your unique request id
                        'Remark'    => 'Payout',
                    ];

                    // cURL GET (BingePay expects GET)
                    $ch = curl_init();
                    $qs = http_build_query($params);
                    curl_setopt_array($ch, [
                        CURLOPT_URL            => $url . '?' . $qs,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING       => '',
                        CURLOPT_MAXREDIRS      => 10,
                        CURLOPT_TIMEOUT        => 30,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST  => 'GET',
                        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                    ]);
                    $raw = curl_exec($ch);
                    $err = curl_error($ch);
                    curl_close($ch);

                    // Log raw
                    try {
                        $lg = new Logs;
                        $lg->uniqueid = 'BINGEPAY_PAYOUT_INIT';
                        $lg->value    = json_encode(['req' => $params, 'res' => $raw, 'err' => $err]);
                        $lg->data1    = $trn_id;
                        $lg->save();
                    } catch (\Throwable $e) {}

                    if ($err || !$raw) {
                        PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                        return response()->json(["status" => false, "message" => "Bank server issue", "id" => $trn_id]);
                    }

                    $res = json_decode($raw, true);
                    // Expected: { "Errorcode":"0", "Status":"1|2|3", "Message":"...", "TxnID":"..." }

                    if (!is_array($res)) {
                        PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                        return response()->json(["status" => false, "message" => "Invalid response", "id" => $trn_id]);
                    }

                    if (($res['Status'] ?? '') === '1') {
                        // Request accepted by BingePay; they will callback later with final status
                        $providerTxn = $res['TxnID'] ?? '';
                        // Debit wallet now (same behavior as other gateways)
                        Wallet::where('userid', $UserData->userid)->decrement('payout', $FinalAmount);
                        // Save provider reference if you want to track
                        PayoutRequest::where('id', $payment_trn->id)->update([
                            "payout_id" => $providerTxn,
                        ]);
                        return response()->json([
                            "status"  => true,
                            "message" => "Success",
                            "id"      => $providerTxn,
                            "dataByBank" => $res
                        ]);
                    }

                    // Status 2 = Processing (treat like accepted OR return processing)
                    if (($res['Status'] ?? '') === '2') {
                        Wallet::where('userid', $UserData->userid)->decrement('payout', $FinalAmount);
                        PayoutRequest::where('id', $payment_trn->id)->update([
                            "payout_id" => $res['TxnID'] ?? ''
                        ]);
                        return response()->json([
                            "status"  => true,
                            "message" => "Success",
                            "id"      => $res['TxnID'] ?? '',
                            "dataByBank" => $res
                        ]);
                    }

                    // Status 3 = Failed or anything else => mark failed
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json([
                        "status"  => false,
                        "message" => $res['Message'] ?? 'Failed',
                        "id"      => $trn_id,
                        "dataByBank" => $res
                    ]);
                } catch (\Throwable $e) {
                    // Safety net
                    try {
                        $lg = new Logs;
                        $lg->uniqueid = 'BINGEPAY_PAYOUT_INIT_ERR';
                        $lg->value    = $e->getMessage();
                        $lg->data1    = $trn_id;
                        $lg->save();
                    } catch (\Throwable $e2) {}
                    PayoutRequest::where('id', $payment_trn->id)->update(["status" => 2]);
                    return response()->json(["status" => false, "message" => "Exception", "id" => $trn_id]);
                }
            } elseif ($flag == 15) {
                // Paydeer Payout Integration
                $paydeer = new \App\Http\Controllers\Gateway\Paydeer();
                $res = $paydeer->createPayout($r->amount, $trn_id, $r->mobile, $r->name, $r->number, $r->ifsc, $UserData->email ?? 'user@example.com');
                
                if ($res && $res->success) {
                    // Check for different possible response field names
                    $payoutId = null;
                    if (isset($res->data['data']['payout_id'])) {
                        $payoutId = $res->data['data']['payout_id'];
                    } elseif (isset($res->data['payout_id'])) {
                        $payoutId = $res->data['payout_id'];
                    } elseif (isset($res->data['id'])) {
                        $payoutId = $res->data['id'];
                    }
                    
                    if ($payoutId) {
                        // Debit wallet amount
                        $Final_amount = $UserWallet->payout - $FinalAmount;
                        Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout generated successfully", "id" => $payoutId, "dataByBank" => $res->data]);
                    } else {
                        return response()->json(["status" => false, "message" => "Xpaisa response received but no payout ID found"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => isset($res->message) ? $res->message : "Payout initiation failed"]);
                }
            } elseif ($flag == 19) {
                // HZTPay Payout Integration
                $hztpay = new \App\Http\Controllers\Gateway\HZTPay($this);
                $res = $hztpay->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['id'];
                        // Debit wallet amount
                        $Final_amount = $UserWallet->payout - $FinalAmount;
                        Wallet::where('userid', $UserData->userid)->update(["payout" => $Final_amount]);
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout generated successfully", "id" => $payoutId]);
                    } else {
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payout initiation failed"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => "Payout initiation failed"]);
                }
            } elseif ($flag == 20) {
                // PayVanta Payout Integration
                $payvanta = new \App\Http\Controllers\Gateway\PayVanta($this);
                $res = $payvanta->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['id'];
                        // Update payout request with PayVanta payout ID
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout generated successfully", "id" => $payoutId]);
                    } else {
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "Payout initiation failed"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => "Payout initiation failed"]);
                }
            } elseif ($flag == 21) {
                // ASVB Payout Integration
                $asvb = new \App\Http\Controllers\Gateway\ASVB($this);
                $res = $asvb->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['id'];
                        // Update payout request with ASVB payout ID
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout generated successfully", "id" => $payoutId]);
                    } else {
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "ASVB payout initiation failed"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => "ASVB payout initiation failed"]);
                }
            } elseif ($flag == 22) {
                // PayPayout Integration
                $paypayout = new \App\Http\Controllers\Gateway\PayPayout($this);
                $res = $paypayout->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['id'];
                        // Update payout request with PayPayout payout ID
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout generated successfully", "id" => $payoutId]);
                    } else {
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "PayPayout initiation failed"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => "PayPayout initiation failed"]);
                }
            } elseif ($flag == 23) {
                // PayU Payout Integration
                $payu = new \App\Http\Controllers\Gateway\PayU($this);
                $res = $payu->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['payout_id'] ?? $responseData['transaction_id']; // PayU returns payout_id, not id
                        // Update payout request with PayU payout ID
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout generated successfully", "id" => $payoutId]);
                    } else {
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "PayU payout initiation failed"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => "PayU payout initiation failed"]);
                }
            } elseif ($flag == 24) {
                // UnitPayGo Payout Integration
                $unitpaygo = new \App\Http\Controllers\Gateway\UnitPayGo($this);
                $res = $unitpaygo->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['data']['apitxnid'] ?? $responseData['data']['txnid'] ?? $responseData['data']['orderid'] ?? '';
                        // Update payout request with UnitPayGo payout ID
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout generated successfully", "id" => $payoutId]);
                    } else {
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "UnitPayGo payout initiation failed"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => "UnitPayGo payout initiation failed"]);
                }
            } elseif ($flag == 25) {
                // Solitpay Payout Integration
                $solitpay = new \App\Http\Controllers\Gateway\Solitpay($this);
                $res = $solitpay->initiatePayout($r);
                
                if ($res && $res->getStatusCode() == 200) {
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] === true) {
                        $payoutId = $responseData['txnid'] ?? $responseData['merchantTxnId'] ?? '';
                        // Update payout request with Solitpay payout ID
                        PayoutRequest::where('id', $payment_trn->id)->update(["payout_id" => $payoutId]);
                        return response()->json(["status" => true, "message" => "Xpaisa payout initiated successfully", "id" => $payoutId]);
                    } else {
                        return response()->json(["status" => false, "message" => $responseData['message'] ?? "Xpaisa payout initiation failed"]);
                    }
                } else {
                    return response()->json(["status" => false, "message" => "Xpaisa payout initiation failed"]);
                }
            }
        }
        return response()->json($response);
    }
    END OF ORPHANED CODE */
    
    public function PayoutStatusCron(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        if (isset($r->trans) && $r->trans != "") {
            $exist = PayoutRequest::leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->where('payout_requests.transaction_id', $r->trans)->get();
        } else {
            $exist = PayoutRequest::where('payout_requests.status', 0)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->get();
        }
        // return $exist;
        if (count($exist) < 0) {
            return response()->json(["status" => "failed", "message" => "Invalid Order Id!!"]);
        }
        foreach ($exist as $data) {
            $poutid = $data->transaction_id;
            $dataByApi = $this->IbrPayoutCheckStatus($poutid);
            // return $dataByApi;
            if ($dataByApi) {
                $IBRstatus = $dataByApi['status'];
                if ($IBRstatus != "failed") {
                    echo $poutid . "| is success by IBR <br>";
                    $status = $dataByApi['data']['Status'];
                    $UTR = $dataByApi['data']['RRN'];
                    $message = $dataByApi['data']['Message'];
                    $flagforCallback = 0;
                    if ($status == "Success") {
                        PayoutRequest::where('transaction_id', $poutid)->update(["status" => 1, "utr" => $UTR, "remark" => $message]);
                        $callbackdata = array("transaction_id" => $poutid, "status" => "success", "utr" => $UTR);
                        $flagforCallback = 1;
                    } elseif ($status == "Refund") {
                        $callbackdata = array("transaction_id" => $poutid, "status" => "failed", "utr" => $UTR);
                        $Final_amount = $data->amount;
                        Wallet::where('userid', $data->userid)->update(["payout" => DB::raw('payout + ' . $Final_amount)]);
                        PayoutRequest::where('transaction_id', $poutid)->update(["status" => 2, "utr" => $UTR, "remark" => $message]);
                        $flagforCallback = 1;
                    } else {
                        echo $poutid . "| is " . $status . " <br>";
                    }
                    if ($data->out_callback != "" && $flagforCallback == 1) {
                        $this->CallbacksendToClient($data->out_callback, json_encode($callbackdata));
                        echo $poutid . "| user callback send " . $callbackdata['status'] . "<br>";
                    }
                    echo $poutid . "| is " . $message . "<br>";
                } else {
                    echo $poutid . "| is failed by IBR <br>";
                    $IBRmessage = $dataByApi['mess'];
                    PayoutRequest::where('transaction_id', $poutid)->update(["status" => 2, "remark" => $IBRmessage]);
                    echo $poutid . "| is " . $IBRmessage . "<br>";
                }
                echo $poutid . " is over!";
            } else {
                echo $poutid . " data not found By API!";
            }
        }
    }
    public function TestPayoutStatusCron(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $exist = PayoutRequest::where('id', '>=', 584)->where('amount', '<', 999)->update(["tax" => "6.844"]);
        return "Success";
        // if(count($exist) < 0){
        //     return response()->json(["status"=>"failed","message"=>"Invalid Order Id!!"]);
        // }
        foreach ($exist as $data) {
            $poutid = $data->transaction_id;
            $dataByApi = $this->IbrPayoutCheckStatus($poutid);
            if ($dataByApi) {
                if (isset($dataByApi['data'])) {
                    $number = $dataByApi['data']['Number'];
                    $ifsc = $dataByApi['data']['IFSC'];
                    $name = $dataByApi['data']['Name'];
                    $IBRstatus = $dataByApi['status'];
                    PayoutRequest::where('transaction_id', $poutid)->update(["account_no" => $number, "ifsc_code" => $ifsc, "remark" => $name]);
                }
                // if($IBRstatus != "failed"){
                //     echo $poutid."| is success by IBR <br>";
                //     $status = $dataByApi['data']['Status'];
                //     $UTR = $dataByApi['data']['RRN'];
                //     $message = $dataByApi['data']['Message'];
                //     $flagforCallback = 0;
                //     if($status == "Success"){
                //         PayoutRequest::where('transaction_id',$poutid)->update(["status"=>1,"utr"=>$UTR,"remark"=>$message]);
                //         $callbackdata = array("transaction_id"=>$poutid,"status"=>"success","utr"=>$UTR);
                //         $flagforCallback = 1;
                //     }elseif($status == "Refund"){
                //         $callbackdata = array("transaction_id"=>$poutid,"status"=>"failed","utr"=>$UTR);
                //         $Final_amount = $data->amount;
                //         Wallet::where('userid',$data->userid)->update(["payout"=>DB::raw('payout + ' . $Final_amount)]);
                //         PayoutRequest::where('transaction_id',$poutid)->update(["status"=>2,"utr"=>$UTR,"remark"=>$message]);
                //         $flagforCallback = 1;
                //     }else{
                //         echo $poutid."| is ".$status." <br>";
                //     }
                //     if($data->out_callback != "" && $flagforCallback == 1){
                //         $this->CallbacksendToClient($data->out_callback,json_encode($callbackdata));
                //         echo $poutid."| user callback send ".$callbackdata['status']."<br>";
                //     }
                //     echo $poutid."| is ".$message."<br>";
                // }else{
                //     echo $poutid."| is failed by IBR <br>";
                //     $IBRmessage = $dataByApi['mess'];
                //     PayoutRequest::where('transaction_id',$poutid)->update(["status"=>2,"remark"=>$IBRmessage]);
                //     echo $poutid."| is ".$IBRmessage."<br>";
                // }
                echo $poutid . " is over!";
            } else {
                echo $poutid . " data not found By API!";
            }
        }
    }
    public function PayoutCheckStatus(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        // return response()->json($response);
        $r->validate([
            'userid' => 'required',
            'orderid' => 'required'
        ]);
        if (!isset($r->userid) || $r->userid == "") {
            return response()->json(["status" => "failed", "message" => "Userid Mandatory"]);
        }
        if (!isset($r->orderid) || $r->orderid == "") {
            return response()->json(["status" => "failed", "message" => "OrderId Mandatory"]);
        }
        $exist = PayoutRequest::where('transaction_id', $r->orderid)->where('userid', $r->userid)->first();
        if (!$exist) {
            return response()->json(["status" => "failed", "message" => "Invalid Order Id!!"]);
        }
        $PaymentStatus = $exist->remark;
        $Status = "failed";
        if ($exist->status == 1) {
            $Status = "success";
        } elseif ($exist->status == 2) {
            $Status = "failed";
        } else {
            $Status = "pending";
            $PaymentStatus = "Bank side pending";
        }
        return response()->json(["status" => $Status, "message" => $PaymentStatus, "utr" => $exist->utr, "client_txn_id" => $exist->transaction_id, "amount" => $exist->amount]);
    }
    public function ibrpayIMSP_callback(Request $r)
    {
        $log = new Logs;
        $log->uniqueid = "ibr";
        $log->value = json_encode($r->all());
        $log->save();
        // return $r->data['OrderID'];
        $event = $r->data['Status'];
        $poutid = $r->data['OrderID'];
        $utr = $r->data['RRN'];
        $remarkGateway = $r->data['Message'];
        if ($event == "Pending") {
        } elseif ($event == "Success") {
            $PayoutData = PayoutRequest::where('transaction_id', $poutid)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->first();
            if ($PayoutData) {
                if ($PayoutData->out_callback != "" && $PayoutData->byApi == 1) {
                    $callbackdata = array("transaction_id" => $poutid, "status" => "success", "utr" => $utr);
                    $this->CallbacksendToClient($PayoutData->out_callback, json_encode($callbackdata));
                }
                PayoutRequest::where('transaction_id', $poutid)->update(["status" => 1, "utr" => $utr]);
            }
        } else {
            $PayoutData = PayoutRequest::where('transaction_id', $poutid)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->first();
            if ($PayoutData) {
                if ($PayoutData->out_callback != "") {
                    $callbackdata = array("transaction_id" => $poutid, "status" => "rejected");
                    $this->CallbacksendToClient($PayoutData->out_callback, json_encode($callbackdata));
                    $Final_amount = $PayoutData->amount;
                    Wallet::where('userid', $PayoutData->userid)->update(["payout" => DB::raw('payout + ' . $Final_amount)]);
                }
                PayoutRequest::where('transaction_id', $poutid)->update(["status" => 2, "remark" => $remarkGateway]);
            }
        }
    }
    public function razorpy_callback(Request $r)
    {
        $json_string = file_get_contents('php://input');
        $log = new Logs;
        $log->value = ($json_string);
        $log->save();
        $initialdaya = json_decode($json_string, true);
        $log = new Logs;
        $log->value = ($json_string);
        $log->save();
        $event = $initialdaya['event'];
        $poutid = $initialdaya['payload']['payout']['entity']['id'];
        $status = $initialdaya['payload']['payout']['entity']['status'];
        $utr = $initialdaya['payload']['payout']['entity']['utr'];
        $remarkGateway = $initialdaya['payload']['payout']['entity']['error']['description'];
        $transaction_id = $initialdaya['payload']['payout']['entity']['reference_id'];
        if ($event == "payout.updated") {
            $PayoutData = PayoutRequest::where('payout_id', $poutid)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->first();
            if ($PayoutData) {
                if ($PayoutData->out_callback != "" && $PayoutData->byApi == 1) {
                    $callbackdata = array("transaction_id" => $transaction_id, "status" => "success", "utr" => $utr);
                    $this->CallbacksendToClient($PayoutData->out_callback, json_encode($callbackdata));
                }
                PayoutRequest::where('payout_id', $poutid)->update(["status" => 1, "utr" => $utr]);
            }
        } elseif ($event == "payout.rejected" || $event == "payout.failed") {
            $PayoutData = PayoutRequest::where('payout_id', $poutid)->leftJoin('users', 'payout_requests.userid', '=', 'users.userid')->select('payout_requests.*', 'users.out_callback')->first();
            if ($PayoutData) {
                if ($PayoutData->out_callback != "") {
                    $callbackdata = array("transaction_id" => $transaction_id, "status" => "rejected");
                    $this->CallbacksendToClient($PayoutData->out_callback, json_encode($callbackdata));
                    $Final_amount = $PayoutData->amount;
                    Wallet::where('userid', $PayoutData->userid)->update(["payout" => DB::raw('payout + ' . $Final_amount)]);
                }
                PayoutRequest::where('payout_id', $poutid)->update(["status" => 2, "remark" => $remarkGateway]);
            }
        }
    }
    public function add_fund(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'amount' => 'required',
        ]);
        if ($r->amount > 0) {
            $charge = ($r->amount / 100) * setting('gateway_phonepe_charge');
            $gst = ($charge / 100) * 18;
            $amount = ($charge + $gst) + $r->amount;
            $trn_id = date('Ymdhis');
            $payment_trn = new Payment_request;
            $payment_trn->transaction_id = $trn_id;
            $payment_trn->userid = user('userid');
            $payment_trn->amount = $r->amount;
            $payment_trn->tax = $charge + $gst;
            $payment_trn->status = 0;
            if ($payment_trn->save()) {
                $finalamountPaid = $charge + $gst + ($r->amount);
                $res = $this->gatewayInitiate($finalamountPaid, $trn_id, 8957287400);
                $r->session()->put('payment_gateway_request', $trn_id);
                if (isset($res->success) && $res->success == '1') {
                    $paymentCode = $res->code;
                    $paymentMsg = $res->message;
                    $payUrl = $res->data->instrumentResponse->redirectInfo->url;
                    $Amount = $finalamountPaid;
                    $trn_id = $trn_id;
                    $IntentUrl = "upi://pay?pa=M22MA4PAMRRA2@ybl&pn=MPay&am=$Amount&mam=$Amount&tr=$trn_id&tn=Payment%20for%20$trn_id&mc=4816&mode=04&purpose=00&utm_campaign=B2B_PG&utm_medium=M22MA4PAMRRA2&utm_source=$trn_id";
                    if ($response) {
                        return redirect($payUrl);
                    }
                    return redirect($payUrl);
                }
            }
        } else {
            $response = array('status' => 0, 'title' => "Amount should be more than 0!!");
        }
        return response()->json($response);
    }

    public function CallbacksendToClient($url, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 2000,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function add_fund_success(Request $request)
    {
        $data = $request->response;
        $r = json_decode(base64_decode($data));
        $log = new Logs;
        $log->value = base64_decode($data);
        $log->save();
        $response = array('status' => false, 'title' => "Invalid action!!");
        if ($r->code == "PAYMENT_SUCCESS") {
            $trn_id = $r->data->transactionId;
            $payment_trn = Payment_request::where('transaction_id', $r->data->merchantTransactionId)->where('status', 0)->first();
            if ($payment_trn) {
                $iNITAEAmount = $payment_trn->amount;
                $payment_trn->data1 = $trn_id;
                $payment_trn->data2 = $r->data->merchantId;
                $payment_trn->status = 1;
                if ($payment_trn->save()) {
                    $amount = $r->data->amount / 100;
                    if ($payment_trn->data3 == 1) {
                        $finalamount = $amount - $payment_trn->tax;
                        addtransaction($payment_trn->userid, 'payin', 'credit', $finalamount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $finalamount, '+', 'payin');
                        $callbackdata = array("status" => "success", "client_txn_id" => $payment_trn->transaction_id, "amount" => $payment_trn->amount, "utr" => $payment_trn->data2);
                        $callback = user('callback', $payment_trn->userid);
                        $this->CallbacksendToClient($callback, json_encode($callbackdata));
                    } else {
                        addtransaction($payment_trn->userid, 'add_fund', 'credit', $iNITAEAmount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $iNITAEAmount, '+', 'wallet');
                    }
                    $response = array('status' => true, 'title' => "Payment Successfully Added!!");
                } else {
                    $response = array('status' => false, 'title' => "Error in data add in order!!");
                }
            } else {
                $response = array('status' => false, 'title' => "Dublicate Transaction!!");
            }
        } else {
            $response = array('status' => false, 'title' => "Something wents wrong!!");
        }
        return response()->json($response);
    }
    public function ccavenue_callback(Request $r)
    {
        $rr = json_encode($r->all());
        $log = new Logs;
        $log->value = $rr;
        $log->save();
        // return $r->order_status;
        $response = array('status' => false, 'title' => "Invalid action!!");
        if ($r->order_status == "Success") {
            $trn_id = $r->bank_ref_no;
            $payment_trn = Payment_request::where('transaction_id', $r->order_id)->where('status', 0)->first();
            if ($payment_trn) {
                $iNITAEAmount = floatval($payment_trn->amount);
                $payment_trn->data1 = $trn_id;
                $payment_trn->data2 = $r->tracking_id;
                $payment_trn->status = 1;
                if ($payment_trn->save()) {
                    $amount = $iNITAEAmount / 100;
                    if ($payment_trn->data3 == 1) {
                        $finalamount = $amount - $payment_trn->tax;
                        addtransaction($payment_trn->userid, 'payin', 'credit', $finalamount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $finalamount, '+', 'payin');
                        $callbackdata = array("status" => "success", "client_txn_id" => $payment_trn->transaction_id, "amount" => $payment_trn->amount, "utr" => $payment_trn->data2);
                        $callback = user('callback', $payment_trn->userid);
                        $this->CallbacksendToClient($callback, json_encode($callbackdata));
                    } else {
                        addtransaction($payment_trn->userid, 'add_fund', 'credit', $iNITAEAmount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $iNITAEAmount, '+', 'wallet');
                    }
                    $response = array('status' => true, 'title' => "Payment Successfully Added!!");
                } else {
                    $response = array('status' => false, 'title' => "Error in data add in order!!");
                }
            } else {
                $response = array('status' => false, 'title' => "Dublicate Transaction!!");
            }
        } else {
            $response = array('status' => false, 'title' => "Something wents wrong!!");
        }
        return response()->json($response);
    }
    public function cashfree_callback(Request $r)
    {
        $rr = json_encode($r->all());
        // $log = new Logs;
        // $log->value = $rr;
        // $log->save();
        // return "ff";
        $response = array('status' => false, 'title' => "Invalid action!!");
        $orderid = $r->data['order']['order_id'];
        $status = $r->data['payment']['payment_status'];
        if ($status == "SUCCESS") {
            $trn_id = $r->data['payment']['bank_reference'];
            $trackingId = $r->data['payment']['cf_payment_id'];
            $payment_trn = Payment_request::where('transaction_id', $orderid)->where('status', 0)->first();
            if ($payment_trn) {
                $iNITAEAmount = floatval($payment_trn->amount);
                $payment_trn->data1 = $trn_id;
                $payment_trn->data2 = $trackingId;
                $payment_trn->status = 1;
                if ($payment_trn->save()) {
                    $amount = $iNITAEAmount;
                    if ($payment_trn->data3 == 1) {
                        $finalamount = $amount - $payment_trn->tax;
                        addtransaction($payment_trn->userid, 'payin', 'credit', $finalamount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $finalamount, '+', 'payin');
                        $callbackdata = array("status" => "success", "client_txn_id" => $payment_trn->transaction_id, "amount" => $payment_trn->amount, "utr" => $payment_trn->data2);
                        $callback = user('callback', $payment_trn->userid);
                        $this->CallbacksendToClient($callback, json_encode($callbackdata));
                    } else {
                        addtransaction($payment_trn->userid, 'add_fund', 'credit', $iNITAEAmount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $iNITAEAmount, '+', 'wallet');
                    }
                    $response = array('status' => true, 'title' => "Payment Successfully Added!!");
                } else {
                    $response = array('status' => false, 'title' => "Error in data add in order!!");
                }
            } else {
                $response = array('status' => false, 'title' => "Dublicate Transaction!!");
            }
        } else {
            $response = array('status' => false, 'title' => "Something wents wrong!!");
        }
        return response()->json($response);
    }
    public function UpiTeladd_fund_success(Request $request)
    {
        // $data = $request->response;
        // $r = json_decode(base64_decode($data));
        // $log = new Logs;
        // $log->value = base64_decode($data);
        // $log->save();
        $json_string = file_get_contents('php://input');
        $initialdaya = json_decode($json_string, true);
        $response = array('status' => false, 'title' => "Invalid action!!");
        if (!isset($initialdaya['status']) || $initialdaya['status'] != "success") {
            return "Status is Not Success";
        }
        $dec = openssl_decrypt($initialdaya['data'], 'AES-128-ECB', "cDd6eWVqbnhhbA==");
        $resp = json_decode($dec, true);
        if (!$resp) {
            return "No data in Encode data";
        }
        // return $resp;
        $cust_mobile = $resp['cust_mobile'];
        $amt = $resp['amt'];
        $utr = $resp['utr'];
        $trxnote = $resp['trxnote'];
        $order_date = $resp['order_date'];
        $orderid = $resp['orderid'];
        // if ($initialdaya->code == "PAYMENT_SUCCESS") {
        $trn_id = $utr;
        $payment_trn = Payment_request::where('transaction_id', $orderid)->where('status', 0)->first();
        if ($payment_trn) {
            $payment_trn->data1 = $trn_id;
            $payment_trn->data2 = $utr;
            $payment_trn->status = 1;
            if ($payment_trn->save()) {
                $amount = $amt;
                if ($payment_trn->data3 == 1) {
                    $finalamount = $amount - $payment_trn->tax;
                    addtransaction($payment_trn->userid, 'payin', 'credit', $finalamount, '', 1, $trn_id);
                    addwallet($payment_trn->userid, $finalamount, '+', 'payin');
                    $callbackdata = array("status" => "success", "client_txn_id" => $payment_trn->transaction_id);
                    $callback = user('callback', $payment_trn->userid);
                    $this->CallbacksendToClient($callback, json_encode($callbackdata));
                } else {
                    addtransaction($payment_trn->userid, 'add_fund', 'credit', $amount, '', 1, $trn_id);
                    addwallet($payment_trn->userid, $amount, '+', 'wallet');
                }
                $response = array('status' => true, 'title' => "Payment Successfully Added!!");
            } else {
                $response = array('status' => false, 'title' => "Error in data add in order!!");
            }
        } else {
            $response = array('status' => false, 'title' => "Dublicate Transaction!!");
        }
        // } else {
        // $response = array('status' => false, 'title' => "Something wents wrong!!");
        // }
        return response()->json($response);
    }
    public function phonepe_redirect_response(Request $r)
    {
        $payment_trn = Payment_request::where('transaction_id', $r->transactionId)->first();
        if ($payment_trn) {
            if ($payment_trn->data3 == 1) {
                return redirect('/checkout/' . $payment_trn->userid);
            } else {
                return redirect('/dashboard');
            }
        } else {
            $response = array('status' => false, 'title' => "Dublicate Transaction!!");
        }
        return response()->json($response);
    }
    public function upitel_redirect_response($trn)
    {
        $payment_trn = Payment_request::where('transaction_id', $trn)->first();
        if ($payment_trn) {
            if ($payment_trn->data3 == 1) {
                return redirect('/checkout/' . $payment_trn->userid);
            } else {
                return redirect('/dashboard');
            }
        } else {
            $response = array('status' => false, 'title' => "Dublicate Transaction!!");
        }
        return response()->json($response);
    }
    public function razorpay_gateway(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        if (session()->has('payment_gateway_request') && session()->get('payment_gateway_request') != '') {
            $session = session()->get('payment_gateway_request');
            $data = Payment_request::where('transaction_id', $session)->where('status', '1')->first();
            return view('frontend.razorpay_gateway', compact('data'));
        }
        return '<h1>Server Access Denied</h1>';
    }
    public function submit_utr($utr, $amount)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        addtransaction(user('userid'), 'add_fund', 'credit', $amount, '', 0, $utr);
        return redirect('/user/add-fund-history');
    }

    public function addtocart(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            "product_id" => 'required',
        ]);
        if (session()->has('userlogin')) {
            $prod = Products::where('id', $r->product_id)->where('stocks', '>', 0)->first();
            $exist = User_Cart::where('pid', $r->product_id)->first();
            if ($exist) {
                $response = array('status' => 0, 'title' => "Product Already exist in cart!!");
            } else {
                if ($prod) {
                    $cart = new User_Cart;
                    $cart->userid = user('userid');
                    $cart->pid = $r->product_id;
                    $cart->name = $prod->name;
                    $cart->amount = $prod->amount;
                    $cart->quantity = 1;
                    if ($cart->save()) {
                        $response = array('status' => 1, 'title' => "Product added in cart successfully!!");
                    }
                } else {
                    $response = array('status' => 0, 'title' => "Out of stock Product!!");
                }
            }
        } else {
            $response = array('status' => 0, 'title' => "Please Login First!!");
        }
        return response()->json($response);
    }
    public function addtowishlist(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            "product_id" => 'required',
        ]);
        if (session()->has('userlogin')) {
            $exist = User_Wishlist::where('pid', $r->product_id)->first();
            if ($exist) {
                $response = array('status' => 0, 'title' => "Product already exist in wishlist!!");
            } else {
                $prod = Products::where('id', $r->product_id)->first();
                if ($prod) {
                    $cart = new User_Wishlist;
                    $cart->userid = user('userid');
                    $cart->pid = $r->product_id;
                    $cart->image = $prod->image1;
                    $cart->name = $prod->name;
                    $cart->amount = $prod->amount;
                    if ($cart->save()) {
                        $response = array('status' => 1, 'title' => "Product added in Wishlist successfully!!");
                    }
                }
            }
        } else {
            $response = array('status' => 0, 'title' => "Please Login First!!");
        }
        return response()->json($response);
    }
    public function deletecartproduct(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = User_Cart::where('id', $r->id)->delete();
        $response = array('status' => 1, 'title' => "Cart Product Deleted Successfully!!");
        return response()->json($response);
    }
    public function deletewishlistproduct(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'id' => 'required',
        ]);
        $category = User_Wishlist::where('id', $r->id)->delete();
        $response = array('status' => 1, 'title' => "Wishlist Product Deleted Successfully!!");
        return response()->json($response);
    }
    public function admin_transactionVerify(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'trnid' => 'required',
            'trnstatus' => 'required',
        ]);
        $existuser = Payment_request::select('payment_requests.*', 'users.callback')->where('transaction_id', $r->trnid)->leftJoin('users', 'payment_requests.userid', '=', 'users.userid')->first();
        if ($existuser) {
            $callbackdata = array("status" => "success", "client_txn_id" => $r->trnid, "amount" => $existuser->amount, "utr" => $r->utr);
            $callback = $existuser->callback;
            if ($existuser->status == $r->trnstatus) {
                return response()->json(array('status' => 0, 'title' => "Already Same Status"));
            }
            Payment_request::where('id', $existuser->id)->update(["status" => $r->trnstatus, "data1" => $r->utr]);
            $finalamount = $existuser->amount - $existuser->tax;
            addwallet($existuser->userid, $finalamount, '+', 'payin');
            $this->CallbacksendToClient($callback, json_encode($callbackdata));
            $response = array('status' => 1, 'title' => "Verified!");
        } else {
            $response = array('status' => 0, 'title' => "Transaction Not Found..");
        }
        return response()->json($response);
    }
    public function admin_amounttransfer(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'wallet' => 'required',
            'transaction_type' => 'required|in:credit,debit',
        ]);

        $existuser = user::where('userid', $r->userid)->where('status', '1')->first();
        if ($existuser) {
            $amount = floatval($r->amount);
            $transaction_type = $r->transaction_type;
            $description = $r->description ?? 'Admin Transaction';
            
            if ($transaction_type === 'debit') {
                // Check if user has sufficient balance
                $userWallet = Wallet::where('userid', $r->userid)->first();
                if (!$userWallet || $userWallet->{$r->wallet} < $amount) {
                    $response = array('status' => 0, 'title' => "Insufficient balance in {$r->wallet} wallet!");
                    return response()->json($response);
                }
                
                // Deduct amount
                addwallet($r->userid, $amount, '-', $r->wallet);
                addtransaction($r->userid, 'admin_deduction', 'debit', $amount, $description, 1, $r->wallet, $r->wallet);
                $response = array('status' => 1, 'title' => "Amount deducted successfully!");
            } else {
                // Add amount
                addwallet($r->userid, $amount, '+', $r->wallet);
                addtransaction($r->userid, 'add_fund', 'credit', $amount, $description, 1, $r->wallet, $r->wallet);
                $response = array('status' => 1, 'title' => "Amount added successfully!");
            }
        } else {
            $response = array('status' => 0, 'title' => "User not found!!");
        }
        return response()->json($response);
    }
    public function admin_amounttransferSelf(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'amount' => 'required',
            'wallet' => 'required',
            'Twallet' => 'required',
        ]);

        $existuser = user::where('userid', $r->userid)->where('status', '1')->first();
        if ($existuser) {
            $Walletmanage = Wallet::where('userid', $r->userid)->first();
            if ($r->amount > $Walletmanage->{$r->wallet}) {
                $response = array('status' => 0, 'title' => "Insufficient Amount!");
                return response()->json($response);
            }
            
            // Get description from request or use default
            $description = $r->description ?? 'Wallet Transfer';
            
            //Other user insert
            addwallet($r->userid, $r->amount, '-', $r->wallet);
            addwallet($r->userid, $r->amount, '+', $r->Twallet);
            addtransaction($r->userid, 'add_fund', 'debit', $r->amount, $description, 1, $r->wallet, $r->wallet);
            addtransaction($r->userid, 'add_fund', 'credit', $r->amount, $description, 1, $r->wallet, $r->Twallet);
            $response = array('status' => 1, 'title' => "Fund Transfer Successfully!");
        } else {
            $response = array('status' => 0, 'title' => "User not found!!");
        }
        return response()->json($response);
    }
    public function admin_payout_mannual(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'userid' => 'required',
            'amount' => 'required',
            'holder_name' => 'required',
            'account_no' => 'required',
            'ifsc_code' => 'required',
            'mode' => 'required',
            'created_at' => 'required'
        ]);

        $existuser = user::where('userid', $r->userid)->where('status', '1')->first();
        if ($existuser) {
            $trn_id = "MPAYPAYOUT_" . date('YmdHis');
            $payment_trn = new PayoutRequest;
            $payment_trn->transaction_id = $trn_id;
            $payment_trn->userid = $r->userid;
            $payment_trn->amount = $r->amount;
            $payment_trn->tax = 0;
            $payment_trn->holder_name = $r->holder_name;
            $payment_trn->account_no = $r->account_no;
            $payment_trn->ifsc_code = $r->ifsc_code;
            $payment_trn->mode = $r->mode;
            $payment_trn->utr = $r->utr;
            $payment_trn->byApi = 1;
            $payment_trn->ip = $r->ip();
            $payment_trn->status = 1;
            $payment_trn->created_at = $r->created_at;
            if ($payment_trn->save()) {
                $response = array('status' => 1, 'title' => "Payout added Successfully!");
            }
        } else {
            $response = array('status' => 0, 'title' => "User not found!!");
        }
        return response()->json($response);
    }
    public function add_user(Request $r)
    {
        $response = array('status' => 0, 'title' => "Invalid action!!");
        $r->validate([
            'username' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'aadhar_no' => 'required',
            'pan_no' => 'required',
            'password' => 'required',
            'company_name' => 'nullable|string|max:255',
            // 'percentage' => 'required',
        ]);

        $existuser = user::where('mobile', $r->mobile)->where('status', '1')->first();
        if ($existuser) {
            return response()->json(array('status' => 0, 'title' => "User Already Registered with mobile no.!"));
        }
        $newid = "GWSDK1111";
        $result = "1111";
        $lastid = user::orderBy('id', 'desc')->where('role', 'user')->first();
        if ($lastid) {
            $newid = $lastid->userid;
            $result = str_replace("GWSDK", "", $newid);
            // Handle old GWS prefix if exists
            if ($result == $newid) {
            $result = str_replace("GWS", "", $newid);
            }
        }
        $result = intval($result) + rand(3, 19);
        $finalid = "GWSDK" . $result;

        $dd = new user;
        $dd->userid = $finalid;
        $dd->name = $r->username;
        $dd->email = $r->email;
        $dd->mobile = $r->mobile;
        $dd->pan_card = $r->pan_no;
        $dd->aadhar_card = $r->aadhar_no;
        $dd->password = Hash::make($r->password);
        $dd->data2 = $r->password;
        $dd->company_name = $r->company_name ?? null; // Store company name
        // $dd->percentage = $r->percentage;
        $dd->token = Hash::make($finalid . "Token");
        $dd->status = 1;
        if ($dd->save()) {
            $response = array('status' => 1, 'title' => "User Successfully Generated!");
        }
        return response()->json($response);
    }
    public function admin_useredit($userid, Request $r)
    {

        $response = array('status' => 0, 'title' => "Oops!! Invalid Action!!", 'message' => "");
        user::where('userid', $userid)->update([
            "name" => $r->name,
            "mobile" => $r->mobile,
            "email" => $r->email,
            "pan_card" => $r->pan_card,
            // "token" => $r->token,
            "aadhar_card" => $r->aadhar_card,
            "callback" => $r->callback,
            "payin_success_redirect" => $r->payin_success_redirect,
            "percentage" => $r->percentage,
            "out_percentage" => $r->out_percentage,
            "payingateway" => $r->payingateway,
            "payoutgateway" => $r->payoutgateway,
            "out_callback" => $r->out_callback,
            "out_ip" => $r->out_ip,
            // Card transaction fields
            "card_percentage" => $r->card_percentage,
            "card_fixed_fee" => $r->card_fixed_fee,
            "card_callback" => $r->card_callback,
            "cardgateway" => $r->cardgateway,
            "card_ip" => $r->card_ip,
            "card_status" => $r->card_status
        ]);
        if (isset($r->bank_name) || isset($r->account_no) || isset($r->ifsc_code)) {
            $exist = User_Bank::where('userid', $userid)->first();
            if ($exist) {
                User_Bank::where('userid', $userid)->update([
                    "bank_name" => $r->bank_name,
                    "account_no" => $r->account_no,
                    "ifsc_code" => $r->ifsc_code,
                ]);
            } else {
                $bank = new User_Bank;
                $bank->userid = $userid;
                if (isset($r->bank_name) && $r->bank_name != '') {
                    $bank->bank_name = $r->bank_name;
                }
                if (isset($r->account_no) && $r->account_no != '') {
                    $bank->account_no = $r->account_no;
                }
                if (isset($r->ifsc_code) && $r->ifsc_code != '') {
                    $bank->ifsc_code = $r->ifsc_code;
                }
                $bank->save();
            }
        }
        $response = array('status' => 1, 'title' => "Update Successfully");
        return response()->json($response);
    }
    public function findRemainBalance($u)
    {
        $total_investment = Investment::where('userid', $u)->where('category', 'credit')->sum('profit');
        $total_withdraw = Investment::where('userid', $u)->where('category', 'debit')->sum('amount');
        $level_income = user::where('userid', $u)->sum('level_income');
        return ($total_investment) - ($total_withdraw + $level_income);
    }
    public function send_level_income($userid, $amount, $level, $lamount, $extra, $type, $percentage, $invest_id, $total_investment)
    {
        $remains = $this->findRemainBalance($userid);
        $alldirect = user::where('sponserid', $userid)->where(function ($query) {
            $query->where('package', 1)->orWhere('product', 1);
        })->count();
        if ($type == 'product') {
            $ppp1 = $alldirect > 0 ? 2 : 1;
        } else {
            $ppp1 = $alldirect > 0 ? 3 : 2;
        }
        if ($remains > 0) {
            Investment::where('id', $userid)->update(['profit' => $total_investment * $ppp1]);
            if ($remains < $amount) {
                $amount = $remains;
            }
            $invest = new Investment;
            $invest->userid = $userid;
            $invest->amount = $amount;
            $invest->data2 = $extra;
            $invest->type = $type;
            $invest->category = 'debit';
            $invest->iid = $invest_id;
            if ($invest->save()) {
                addwallet($userid, $amount, '+', 'wallet');
                addtransaction($userid, 'sponser_income', 'credit', $amount, null, 1);
                for ($i = 0; $i < count($level); $i++) {
                    $u = $level[$i]->userid;
                    $ActiveUser = isActive($level[$i]->userid);
                    $a = $lamount[$i];
                    $total_team = user::where('sponserid', $u)->where(function ($query) {
                        $query->where('package', 1)->orWhere('product', 1);
                    })->count();
                    $pp1 = $total_team > 0 ? 3 : 2;
                    $pp2 = $total_team > 0 ? 2 : 1;
                    $remain = $this->findRemainBalance($u);
                    // echo "Userid:".$userid."| Amount:".$amount."| LUser:".$u."| Amount:".$a." Team size to: ".$total_team."| I: ".$i."Remains: ".$remain."<br>";
                    if ($remain <= 0) {
                        Investment::where('userid', $u)->where('category', 'credit')->update(['status' => 0]);
                    }
                    if ($remain > 0 && $total_team >= ($i + 1) && $ActiveUser) {
                        if ($remain < $a) {
                            $a = $remain;
                        }
                        addwallet($u, $a, '+', 'wallet');
                        addtransaction($u, 'level_income', 'credit', $a, 1, 1, $userid, ($i + 1), $type);
                        User::where('userid', $u)->update(['level_income' => DB::raw('level_income + ' . $a)]);
                    }
                }
            }
        }
    }
    public function levelincomedistribute()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://web-api.phonepe.com/apis/mi-web/v2/auth/web/login/initiate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "type": "OTP_V2",
    "endpoint": "7060471592",
    "channelType": "SMS",
    "deviceFingerprint": "123"
}',
            CURLOPT_HTTPHEADER => array(
                'X-Csrf-Token: ' . csrf_token(),
                'Content-Type: application/json',
                'Cookie: _CKB2N1BHVZ=xcu/6iqAsjGF2axUe2Cl2TePk4tnNe9MiFhF07SHVUybBOANVkdaRzU1Xrtfu2Cz'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

    }
    public function levelincomedistribute_month()
    {
        $alluser = user::where('product', '>', 0)->where('status', 1)->where('isadmin', null)->get();
        foreach ($alluser as $user) {
            $type = 'product';
            $user_investment = Investment::where('userid', $user->userid)->where('category', 'credit')->where('type', $type)->where('status', 1)->get();
            foreach ($user_investment as $in) {
                $total_investment = $in->amount;
                $total_withdraw = Investment::where('userid', $user->userid)->where('category', 'debit')->where('type', $type)->where('iid', $in->id)->sum('amount');
                if ($total_investment <= $total_withdraw) {
                    Investment::where('id', $in->id)->update(['status' => 0]);
                }
                $remain = ($total_investment / 100) * 1.5;
                $percent = [percentage($remain, 15), percentage($remain, 10), percentage($remain, 5), percentage($remain, 5), percentage($remain, 5), percentage($remain, 5), percentage($remain, 5)];
                $useralldownline = alllevel($user->userid);
                if ($total_investment > $total_withdraw) {
                    $this->send_level_income($user->userid, $remain, $useralldownline, $percent, 0, $type, 1, $in->id, $total_investment);
                }
            }
        }
        return "All settlement are cleared";
    }
    public function payinCallbackRudraxpay(Request $request)
    {
        $response = array('status' => false, 'title' => "Invalid action!!");
        if (!isset($request->status)) {
            return "Status is Not Founded";
        }
        $status = $request->status;
        $amt = $request->amount;
        $utr = $request->utr;
        $orderid = $request->client_txn_id;
        $outstatus = "failed";
        $flag = 0;
        $trn_id = $utr;
        $payment_trn = Payment_request::where('transaction_id', $orderid)->where('status', 0)->first();
        if ($payment_trn) {
            $payment_trn->data1 = $trn_id;
            $payment_trn->data2 = $utr;
            if ($status == "success") {
                $flag = 1;
                $payment_trn->status = 1;
            } elseif ($status == "failed") {
                $payment_trn->status = 2;
            }
            if ($payment_trn->save()) {
                $amount = $amt;
                if ($payment_trn->data3 == 1) {
                    if ($flag == 1) {
                        $outstatus = "success";
                        $finalamount = $amount - $payment_trn->tax;
                        addtransaction($payment_trn->userid, 'payin', 'credit', $finalamount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $finalamount, '+', 'payin');
                    }
                    $callbackdata = array("status" => $outstatus, "client_txn_id" => $payment_trn->transaction_id, "utr" => $utr, "amount" => $amount);
                    $callback = user('callback', $payment_trn->userid);
                    $this->CallbacksendToClient($callback, json_encode($callbackdata));
                } else {
                    if ($flag == 1) {
                        addtransaction($payment_trn->userid, 'add_fund', 'credit', $amount, '', 1, $trn_id);
                        addwallet($payment_trn->userid, $amount, '+', 'wallet');
                    }
                }
                $response = array('status' => true, 'title' => "Payment Successfully Added!!");
            } else {
                $response = array('status' => false, 'title' => "Error in data add in order!!");
            }
        } else {
            $response = array('status' => false, 'title' => "Dublicate Transaction!!");
        }
        return response()->json($response);
    }
    public function clean()
    {
        // Investment::where('category', 'debit')->delete();
        // user::where('product', '!=', 0)->orWhere('package', '!=', 0)->update(['product' => 0, 'package' => 0, 'level_income' => 0]);
        // wallet::where('total_balance', '!=', 0)->update(['wallet' => 0, 'total_balance' => 0]);
        // Transaction::where('id', '!=', 0)->delete();
        Artisan::call('make:model Logs');
        dd('cleared');
    }

    /**
     * Generalized Card Transaction Function
     * Handles card transactions for different providers
     */
    public function card_transaction(Request $request)
    {
        try {
            // Validate required fields
            $request->validate([
                'token' => 'required|string',
                'userid' => 'required|string',
                'amount' => 'required|numeric',
                'currency' => 'required|string',
                'reference' => 'required|string',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'cardName' => 'required|string',
                'cardNumber' => 'required|string',
                'cardCVV' => 'required|string',
                'expMonth' => 'required|string',
                'expYear' => 'required|string',
                'country' => 'required|string',
                'city' => 'required|string',
                'address' => 'required|string',
                'ip_address' => 'required|string',
                'zip_code' => 'required|string',
                'state' => 'required|string',
                'callback_url' => 'required|url'
            ]);

            // Get user card configuration to determine the gateway
            $userCardConfig = usercardconfig($request->userid);
            if (!$userCardConfig) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], 404);
            }

            // Check if user card transactions are active
            if (!isCardActive($request->userid)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Card transactions are not active for this user'
                ], 403);
            }

            // Get the user's configured card gateway
            $cardGateway = getCardGatewayName($request->userid);
            if ($cardGateway === 'No Gateway' || $cardGateway === 'Unknown Gateway') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No valid card gateway configured for user'
                ], 400);
            }

            // Route to appropriate gateway controller based on user configuration
            switch (strtolower($cardGateway)) {
                case 'swipepointe':
                    // Use SwipePointe Gateway controller
                    $swipepointeController = new \App\Http\Controllers\Gateway\SwipePointe($this);
                    $response = $swipepointeController->processCardTransaction($request);
                    break;
                
                case 'easebuzz':
                    // Use Easebuzz Gateway controller for card transactions
                    $easebuzzController = new \App\Http\Controllers\Gateway\Easebuzz($this);
                    $response = $easebuzzController->processCardTransaction($request);
                    break;
                
                // Add more providers here in future
                // case 'razorpay card':
                //     $razorpayController = new \App\Http\Controllers\Gateway\RazorpayCard();
                //     $response = $razorpayController->processCardTransaction($request);
                //     break;
                
                default:
                    $response = [
                        'status' => 'failed',
                        'message' => 'Unsupported card gateway: ' . $cardGateway
                    ];
                    break;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirect to Paydeer payment URL with masked domain
     */
    public function paydeerRedirect($token)
    {
        try {
            // Decode the token to get the actual Paydeer URL
            $actualUrl = base64_decode($token);
            
            // Validate that it's a Paydeer URL for security
            if (!str_contains($actualUrl, 'payin.paydeer.in')) {
                return redirect('/')->with('error', 'Invalid payment link');
            }
            
            // Redirect to the actual Paydeer URL
            return redirect($actualUrl);
            
        } catch (\Exception $e) {
            \Log::error('Paydeer redirect error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Payment link expired or invalid');
        }
    }
    
}

