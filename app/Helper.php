<?php

use App\Models\Admin_Wallet;
use App\Models\Category;
use App\Models\club_user;
use App\Models\Packages;
use App\Models\Products;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Transaction_password;
use App\Models\user;
use App\Models\User_Bank;
use App\Models\Wallet;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function ALLgateway($p) {
    $arr = array();
    
    if ($p == 1) {
        $arr = array(
            // 1 => "phonepe",
            // 2 => "upitel",
            // 3 => "ccavenue",
            100 => "No PG",
            // 4 => "Cashfree",
            // 5 => "MannualBharatPe",
            //6 => "Razorpay",
            // 7 => "Sabpaisa",
            // 8 => "Payomatix",
             //9 => "Runpaisa",
            // 10 => "Usdpay",
            // 12 => "NsdlPay",
            //13 => "T1 Rudraxpay",
            // 14 => "FinQunes",
            //15 => "Razorpay Card",
            // 16 => "BingePay",
            //17 => "QutePaisa",
            // 18 => "Paydeer",
            // 19 => "HZTPay",
            // 20 => "PayVanta",
            // 21 => "ASVB",
            // 23 => "PayU",
            // 24 => "UnitPayGo",
            // 25 => "Solitpay",
            // 26 => "NSO",
            // 27 => "Spay",
            28 => "Easebuzz", // ✅ ONLY EASEBUZZ FOR PAYIN
            29 => "AuroPay",  // ✅ AUROPAY - Payment Link + QR Code
        );
    } elseif ($p == 2) {
        $arr = array(
            // 1 => "IBR",
            // 2 => "Razorpay",
            // 3 => "WaoPay",
            100 => "No Payout",
            // 4 => "Universe Pay",
            // 5 => "usdpay",
            // 6 => "Cash Kavach",
            // 7 => "SafexPay Touras",
            // 8 => "Uniqpay",
            // 9 => "VeloZPay",
            // 10 => "Rudraxpay",
            // 11 => "BenakPay",
            // 12 => "FinQunes",
            // 13 => "BingePay",
            //14 =>"QutePaisa",
            // 15 => "Paydeer",
            // 19 => "HZTPay",
            // 20 => "PayVanta",
            // 21 => "ASVB",
            // 22 => "PayPayout",
            // 23 => "PayU",
            // 24 => "UnitPayGo",
            // 25 => "Solitpay",
            // 27 => "Spay",
            28 => "Easebuzz", // ✅ ONLY EASEBUZZ FOR PAYOUT
        );
    } elseif ($p == 3) {
        // Card payment gateways
        $arr = array(
            100 => "No Card PG",
            1 => "SwipePointe",
            2 => "Razorpay Card",
            3 => "Easebuzz",
            // Add more card providers here
            // 4 => "Stripe",
            // 5 => "PayPal",
        );
    }
    
    return $arr;
}

/**
 * Get gateway name by ID
 */
function gateway_name($gatewayId, $type = 'payout') {
    if ($type === 'payout') {
        $gateways = [
            // '10' => 'Rudraxpay',
            // '12' => 'FinQunes',
            // '15' => 'Paydeer',
            // '19' => 'HZTPay',
            // '20' => 'PayVanta',
            // '21' => 'ASVB',
            // '22' => 'PayPayout',
            // '23' => 'PayU',
            // '24' => 'UnitPayGo',
            // '25' => 'Solitpay',
            // '26' => 'NSO',
            // '27' => 'Spay',
            '28' => 'Easebuzz', // ✅ ONLY EASEBUZZ
            '100' => 'No Payout',
        ];
    } else {
        $gateways = [
            // '14' => 'FinQunes',
            // '18' => 'Paydeer',
            // '19' => 'HZTPay',
            // '20' => 'PayVanta',
            // '21' => 'ASVB',
            // '23' => 'PayU',
            // '24' => 'UnitPayGo',
            // '25' => 'Solitpay',
            // '26' => 'NSO',
            // '27' => 'Spay',
            '28' => 'Easebuzz', // ✅ ONLY EASEBUZZ
            '29' => 'AuroPay',  // ✅ AUROPAY
            '100' => 'No PG',
        ];
    }
    
    return $gateways[$gatewayId] ?? 'Unknown Gateway (' . $gatewayId . ')';
}

function encrypts($text, $key, $type, $iv = "0123456789abcdef",
$size = 16) {
$pad = $size - (strlen($text) % $size);
$padtext = $text . str_repeat(chr($pad), $pad);
$crypt = openssl_encrypt($padtext, "AES-256-CBC",
base64_decode($key),OPENSSL_RAW_DATA
|OPENSSL_ZERO_PADDING, $iv);
return base64_encode($crypt);
}
function decrypts($crypt, $key, $type, $iv = "0123456789abcdef") {
$crypt = base64_decode($crypt);
$padtext = openssl_decrypt($crypt, "AES-256-CBC",
base64_decode($key),OPENSSL_RAW_DATA
|OPENSSL_ZERO_PADDING, $iv);
$pad = ord($padtext
[strlen($padtext) - 1]); if ($pad > strlen($padtext)) {
return false;}
if (strspn($padtext, $padtext
[strlen($padtext) - 1], strlen($padtext) - $pad) != $pad) {
$text = "Error";
}
}
function generateBasicAuth($apiKey, $apiSecret)
{
    // Combine the API key and secret with a colon
    $authString = $apiKey . ':' . $apiSecret;
    
    // Encode the string in Base64
    $base64AuthString = base64_encode($authString);
    
    // Return the Basic Auth header string
    return 'Basic ' . $base64AuthString;
}

function evpkdf($Key, $salt)
{
    $salted = '';
    $dx = '';
    while (strlen($salted) < 48) {
        $dx = md5($dx . $Key . $salt, true);
        $salted .= $dx;
    }
    $key = substr($salted, 0, 32);
    $iv = substr($salted, 32, 16);
    return [$key, $iv];
}

function encrypt_ibr($json_data, $Key, $salt = null)
{
    $salt = $salt ?: openssl_random_pseudo_bytes(8);
    list($key, $iv) = evpkdf($Key, $salt);
    $ct = openssl_encrypt($json_data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode("Salted__" . $salt . $ct);
}

function setting($parameter)
{
    $data = Setting::where('name', $parameter)->first();
    if ($data) {
        return $data->value;
    }
}

function CallbacksendToClientAdarsh($url,$data){
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

function balance($amount, $curr = '$')
{
    return '₹ ' . number_format(floatval($amount), 3);
    // $ex = setting('exchange_rate');
}

function formatCurrency($amount, $currency = 'USD')
{
    $currencySymbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹',
        'JPY' => '¥',
        'CAD' => 'C$',
        'AUD' => 'A$',
        'CHF' => 'CHF',
        'CNY' => '¥',
        'SGD' => 'S$',
        'HKD' => 'HK$',
        'KRW' => '₩',
        'BRL' => 'R$',
        'MXN' => '$',
        'RUB' => '₽',
        'ZAR' => 'R',
        'SEK' => 'kr',
        'NOK' => 'kr',
        'DKK' => 'kr',
        'PLN' => 'zł',
        'CZK' => 'Kč',
        'HUF' => 'Ft',
        'RON' => 'lei',
        'BGN' => 'лв',
        'HRK' => 'kn',
        'TRY' => '₺',
        'ILS' => '₪',
        'AED' => 'د.إ',
        'SAR' => 'ر.س',
        'QAR' => 'ر.ق',
        'KWD' => 'د.ك',
        'BHD' => 'د.ب',
        'OMR' => 'ر.ع.',
        'JOD' => 'د.أ',
        'LBP' => 'ل.ل',
        'EGP' => 'ج.م',
        'NGN' => '₦',
        'GHS' => '₵',
        'KES' => 'KSh',
        'UGX' => 'USh',
        'TZS' => 'TSh',
        'ZMW' => 'ZK',
        'NAD' => 'N$',
        'BWP' => 'P',
        'MWK' => 'MK',
        'MUR' => '₨',
        'SCR' => '₨',
        'SZL' => 'E',
        'LSL' => 'L',
        'MAD' => 'د.م.',
        'TND' => 'د.ت',
        'LYD' => 'ل.د',
        'DZD' => 'د.ج',
        'XOF' => 'CFA',
        'XAF' => 'FCFA',
        'XPF' => 'CFP',
        'CLP' => '$',
        'PEN' => 'S/',
        'COP' => '$',
        'ARS' => '$',
        'UYU' => '$',
        'PYG' => '₲',
        'BOB' => 'Bs',
        'VND' => '₫',
        'THB' => '฿',
        'MYR' => 'RM',
        'IDR' => 'Rp',
        'PHP' => '₱',
        'BDT' => '৳',
        'LKR' => 'Rs',
        'NPR' => '₨',
        'PKR' => '₨',
        'AFN' => '؋',
        'IRR' => '﷼',
        'IQD' => 'ع.د',
        'KWD' => 'د.ك',
        'BHD' => 'د.ب',
        'OMR' => 'ر.ع.',
        'JOD' => 'د.أ',
        'LBP' => 'ل.ل',
        'EGP' => 'ج.م',
        'NGN' => '₦',
        'GHS' => '₵',
        'KES' => 'KSh',
        'UGX' => 'USH',
        'TZS' => 'TSh',
        'ZMW' => 'ZK',
        'NAD' => 'N$',
        'BWP' => 'P',
        'MWK' => 'MK',
        'MUR' => '₨',
        'SCR' => '₨',
        'SZL' => 'E',
        'LSL' => 'L',
        'MAD' => 'د.م.',
        'TND' => 'د.ت',
        'LYD' => 'ل.د',
        'DZD' => 'د.ج',
        'XOF' => 'CFA',
        'XAF' => 'FCFA',
        'XPF' => 'CFP'
    ];
    
    $symbol = $currencySymbols[strtoupper($currency)] ?? $currency;
    $decimals = in_array(strtoupper($currency), ['JPY', 'KRW', 'VND', 'XOF', 'XAF', 'XPF']) ? 0 : 2;
    
    return $symbol . ' ' . number_format(floatval($amount), $decimals);
}

function user($parameter, $userid = null)
{
    if ($userid == null) {
        if (session()->has('userlogin')) {
            $id = session()->get('userlogin')->id;
            $data = user::where('id', $id)->first();
            if ($data) {
                return $data->{$parameter};
            }
            return session()->get('userlogin')->{$parameter};
        }
    } else {
        $data = user::where('userid', $userid)->first();
        if ($data) {
            return $data->{$parameter};
        }
    }
}

function userbyuserid($userid, $parameter)
{
    $data = user::where('userid', $userid)->first();
    if ($data) {
        return $data->{$parameter};
    }
    return;
}

function admin($parameter)
{
    if (session()->has('adminlogin')) {
        $id = session()->get('adminlogin')->id;
        $data = user::where('id', $id)->first();
        if ($data) {
            return $data->{$parameter};
        }
        return session()->get('adminlogin')->{$parameter};
    }
}

function _check($parameter, $message = null)
{
    if (isset($parameter) && $parameter != '') {
        return $parameter;
    }
    return $message;
}
function datealgebra($date, $operator, $value, $format = "Y-m-d")
{
    if ($operator == "-") {
        $date = date_create($date);
        date_sub($date, date_interval_create_from_date_string($value));
        return date_format($date, $format);
    } elseif ($operator == "+") {
        $date = date_create($date);
        date_add($date, date_interval_create_from_date_string($value));
        return date_format($date, $format);
    }
}
function isActive($userid, $type = "bool")
{
    $u = User::where('userid', $userid)
        ->where(function ($query) {
            $query->where('package', 1)
                ->orWhere('product', 1);
        })
        ->first();
    if ($u) {
        if ($type == "string") {
            if ($u->product == 1 && $u->package) {
                return 'Product,Stack';
            } elseif ($u->product == 1) {
                return 'Product';
            } elseif ($u->package == 1) {
                return 'Stack';
            }
        }
        return true;
    } else {
        if ($type == "string") {
            return 'Inactive';
        }
    }
    return false;
}
function dformat($date, $format)
{
    if ($date == null) {
        return;
    }
    $strd = date_create($date);
    return date_format($strd, $format);
}
function differenceDate($datebg, $datesm, $format = "%d")
{
    if ($datesm == "") {
        $datesm = date('d-m-Y h:i:s');
    }
    $date1 = new DateTime($datesm);
    $date2 = new DateTime($datebg);
    $difference = $date1->diff($date2)->format($format);
    return $difference;
}

function packages($id, $parameter, $type = null)
{
    $data = Packages::where('id', $id)->first();
    if ($data) {
        return $data->{$parameter};
    }
    return 'Inactive';
}
function bank($id, $parameter)
{
    $data = User_Bank::where('userid', $id)->first();
    if ($data) {
        return $data->{$parameter};
    }
    return false;
}

function category($id, $parameter)
{
    $data = Category::where('id', $id)->first();
    if ($data) {
        return $data->{$parameter};
    }
    return 0;
}

function wallet($userid, $type = null, $wallettype = null)
{
    $lastDate = date('Y-m-d');
    $todayDate = date('Y-m-d');
    $lastTransaction = Setting::where('name','last_settlement')->first();
    if($lastTransaction){
        $lastDate = $lastTransaction->value;
    }
    if($wallettype == "payin"){
        $data = DB::select("SELECT SUM(payment_requests.amount-payment_requests.tax) as ATotal FROM users LEFT JOIN payment_requests ON users.userid = payment_requests.userid WHERE DATE(payment_requests.created_at) <= '$todayDate' AND DATE(payment_requests.created_at) >= '$lastDate' AND (payment_requests.data3 = 1 OR payment_requests.data3 LIKE 'intent_%' OR payment_requests.data3 LIKE '%-%-%-%-%') AND payment_requests.status = 1 AND users.userid = '".$userid."'");
        if(isset($data[0]->ATotal) && $data[0]->ATotal != null){
            return $data[0]->ATotal;
        }
        return 0;
    }
    $amounts = Wallet::where('userid', $userid)->first();
    // return $amounts;
    $tbalance = 0;
    if ($amounts) {
        if ($wallettype != null) {
            $tbalance = $amounts->{$wallettype};
        } else {
            $tbalance = $amounts->amount;
        }
    }
    if ($type == null) {
        return number_format($tbalance, 2);
    }
    return $tbalance;
}
function imageupload($file, $name, $path)
{
    $file_name = "";
    $file_type = "";
    $filePath = "";
    $size = "";

    if ($file) {
        $file_name = $file->getClientOriginalName();
        $file_type = $file->getClientOriginalExtension();
        $fileName = $name . "." . $file_type;
        Storage::disk('public')->put($path . $fileName, File::get($file));
        $filePath = "/" . 'storage/' . $path . $fileName;
    }
    return $file = [
        'fileName' => $file_name,
        'fileType' => $file_type,
        'filePath' => $filePath,
    ];
}
function addwallet($userid, $amount, $operator = '+', $wallettype = null)
{
    $exist = Wallet::where('userid',$userid)->first();
    $old = 0;
    $oldtotal = 0;
    if(!$exist){
        $dd = new Wallet;
        $dd->userid = $userid;
        $dd->save();
    }else{
        $old = $exist->{$wallettype};
        $oldtotal = $exist->total_balance;
    }
    if ($operator == '+') {
        $FinalQuery = Wallet::where('userid', $userid)->update([
            $wallettype => $old + floatval($amount),
            'total_balance' => $oldtotal + floatval($amount),
        ]);
        $FinalQuerys = $old + floatval($amount);
    } elseif ($operator == '-') {
        $finalamount = $old - floatval($amount);
        $FinalQuery = Wallet::where('userid', $userid)->update([
            $wallettype => $finalamount,
            'total_balance' => $oldtotal - floatval($amount),
        ]);
        $FinalQuerys = $old - floatval($amount);
    } elseif ($operator == '*') {
        $FinalQuery = Wallet::where('userid', $userid)->update([
            $wallettype => $old * floatval($amount),
            'total_balance' => $oldtotal * floatval($amount),
        ]);
        $FinalQuerys = $old * floatval($amount);
    }
    if(isset($FinalQuerys)){
        return $FinalQuerys;
    }
    return 0;
}
function findsenior($userid, $parameter)
{
    $userheadsponser = user::where('userid', $userid)->where('status', '1')->where('isadmin', null)->first();
    if ($userheadsponser) {
        $data = user::where('userid', $userheadsponser->sponserid)->where('status', '1')->where('isadmin', null)->first();
        if ($data) {
            return $data->{$parameter};
        }
    }
    return null;
}
function alllevel($userid)
{
    $all_user = [];
    $temp_user = user::where('userid', $userid)->first();
    if ($temp_user) {
        $temp_users = $temp_user->sponserid;
        for ($i = 0; $i < 7; $i++) {
            $userheadsponser = user::where('userid', $temp_users)->first();
            if ($userheadsponser) {
                $temp_users = $userheadsponser->sponserid;
                array_push($all_user, $userheadsponser);
            }
        }
    }
    return $all_user;
}
function addtransaction($userid, $category, $type, $amount, $remark, $status, $data2 = null, $data3 = null, $data4 = null, $data5 = null, $data6 = null, $data7 = null)
{
    $t = new Transaction;
    $t->userid = $userid;
    $t->amount = $amount;
    $t->category = $category;
    $t->type = $type;
    $t->data1 = $remark;
    $t->status = $status;
    if ($data2 != null) {
        $t->data2 = $data2;
    }
    if ($data3 != null) {
        $t->data3 = $data3;
    }
    if ($data4 != null) {
        $t->data4 = $data4;
    }
    if ($data5 != null) {
        $t->data5 = $data5;
    }
    if ($data6 != null) {
        $t->data6 = $data6;
    }
    if ($data7 != null) {
        $t->data7 = $data7;
    }
    if ($t->save()) {
        return $t->id;
    }
    return false;
}

function product($id, $parameter)
{
    $data = Products::where('id', $id)->first();
    if ($data) {
        return $data->{$parameter};
    }
}
function leveldetail($userid)
{
    $alllevel = array();

    $l1g = array();
    $l1g_paid_user = array();

    $l2g = array();
    $l2g_paid_user = array();

    $l3g = array();
    $l3g_paid_user = array();

    $l4g = array();
    $l4g_paid_user = array();

    $l5g = array();
    $l5g_paid_user = array();

    $l6g = array();
    $l6g_paid_user = array();

    $l7g = array();
    $l7g_paid_user = array();

    $g = User::where('userid', $userid)->first();
    if ($g) {
        $sponserId = $g->userid;
        $level1 = User::where('sponserid', $sponserId)->orderBy('id', 'asc')->get();
        foreach ($level1 as $row) {
            if ($row->package > 0 || $row->product > 0) {
                array_push($l1g_paid_user, $row);
            }
            array_push($l1g, $row);
        }
        foreach ($level1 as $item) {
            $level2 = User::where('sponserid', $item->userid)->orderBy('id', 'asc')->get();
            foreach ($level2 as $key) {
                if ($key->package > 0 || $key->product > 0) {
                    array_push($l2g_paid_user, $key);
                }
                array_push($l2g, $key);
            }
            foreach ($level2 as $item2) {
                $level3 = User::where('sponserid', $item2->userid)->orderBy('id', 'asc')->get();
                foreach ($level3 as $key) {
                    if ($key->package > 0 || $key->product > 0) {
                        array_push($l3g_paid_user, $key);
                    }
                    array_push($l3g, $key);
                }
                foreach ($level3 as $item3) {
                    $level4 = User::where('sponserid', $item3->userid)->orderBy('id', 'asc')->get();
                    foreach ($level4 as $key) {
                        if ($key->package > 0 || $key->product > 0) {
                            array_push($l4g_paid_user, $key);
                        }
                        array_push($l4g, $key);
                    }
                    foreach ($level4 as $item4) {
                        $level5 = User::where('sponserid', $item4->userid)->orderBy('id', 'asc')->get();
                        foreach ($level5 as $key) {
                            if ($key->package > 0 || $key->product > 0) {
                                array_push($l5g_paid_user, $key);
                            }
                            array_push($l5g, $key);
                        }
                        foreach ($level5 as $item5) {
                            $level6 = User::where('sponserid', $item5->userid)->orderBy('id', 'asc')->get();
                            foreach ($level6 as $key) {
                                if ($key->package > 0 || $key->product > 0) {
                                    array_push($l6g_paid_user, $key);
                                }
                                array_push($l6g, $key);
                            }
                            foreach ($level6 as $item6) {
                                $level7 = User::where('sponserid', $item6->userid)->orderBy('id', 'asc')->get();
                                foreach ($level7 as $key) {
                                    if ($key->package > 0 || $key->product > 0) {
                                        array_push($l7g_paid_user, $key);
                                    }
                                    array_push($l7g, $key);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    array_push($alllevel, [
        "name" => 'level1',
        'data' => $l1g,
        'paid_data' => $l1g_paid_user,
        'tuser' => count($l1g),
        'tpaid_user' => count($l1g_paid_user),
    ]);
    array_push($alllevel, [
        "name" => 'level2',
        'data' => $l2g,
        'paid_data' => $l2g_paid_user,
        'tuser' => count($l2g),
        'tpaid_user' => count($l2g_paid_user),
    ]);
    array_push($alllevel, [
        "name" => 'level3',
        'data' => $l3g,
        'paid_data' => $l3g_paid_user,
        'tuser' => count($l3g),
        'tpaid_user' => count($l3g_paid_user),
    ]);
    array_push($alllevel, [
        "name" => 'level4',
        'data' => $l4g,
        'paid_data' => $l4g_paid_user,
        'tuser' => count($l4g),
        'tpaid_user' => count($l4g_paid_user),
    ]);
    array_push($alllevel, [
        "name" => 'level5',
        'data' => $l5g,
        'paid_data' => $l5g_paid_user,
        'tuser' => count($l5g),
        'tpaid_user' => count($l5g_paid_user),
    ]);
    array_push($alllevel, [
        "name" => 'level6',
        'data' => $l6g,
        'paid_data' => $l6g_paid_user,
        'tuser' => count($l6g),
        'tpaid_user' => count($l6g_paid_user),
    ]);
    array_push($alllevel, [
        "name" => 'level7',
        'data' => $l7g,
        'paid_data' => $l7g_paid_user,
        'tuser' => count($l7g),
        'tpaid_user' => count($l7g_paid_user),
    ]);
    return $alllevel;
}
function findefficiency($sponserid)
{
    $alllevel = leveldetail($sponserid);
    foreach ($alllevel as $level) {
        // return $level['paid_data'][0]['userid'];
        foreach ($level['paid_data'] as $leveluser) {
            $persons = user::where('sponserid', $leveluser['userid'])->count();
            if ($persons < 4 && $sponserid != $leveluser['userid']) {
                return $leveluser['userid'];
            }
        }
    }
    return false;
}
function rankdetail($userid)
{
    $alllevel = array();

    $p1 = array();
    $p2 = array();
    $p3 = array();
    $p4 = array();
    $p5 = array();

    $g = User::where('userid', $userid)->first();
    if ($g) {
        $sponserId = $g->userid;
        $level1 = User::where('sponserid', $sponserId)->orderBy('id', 'desc')->get();
        foreach ($level1 as $key) {
            if ($key->package == 1) {
                array_push($p1, $key);
            }
            if ($key->package == 2) {
                array_push($p2, $key);
            }
        }
        foreach ($level1 as $item) {
            $level2 = User::where('sponserid', $item->userid)->orderBy('id', 'desc')->get();
            foreach ($level2 as $key) {
                if ($key->package == 1) {
                    array_push($p1, $key);
                }
                if ($key->package == 2) {
                    array_push($p2, $key);
                }
            }
            foreach ($level2 as $item2) {
                $level3 = User::where('sponserid', $item2->userid)->orderBy('id', 'desc')->get();
                foreach ($level3 as $key) {
                    if ($key->package == 1) {
                        array_push($p1, $key);
                    }
                    if ($key->package == 2) {
                        array_push($p2, $key);
                    }
                }
                foreach ($level3 as $item3) {
                    $level4 = User::where('sponserid', $item3->userid)->orderBy('id', 'desc')->get();
                    foreach ($level4 as $key) {
                        if ($key->package == 1) {
                            array_push($p1, $key);
                        }
                        if ($key->package == 2) {
                            array_push($p2, $key);
                        }
                    }
                    foreach ($level4 as $item4) {
                        $level5 = User::where('sponserid', $item4->userid)->orderBy('id', 'desc')->get();
                        foreach ($level5 as $key) {
                            if ($key->package == 1) {
                                array_push($p1, $key);
                            }
                            if ($key->package == 2) {
                                array_push($p2, $key);
                            }
                        }
                        foreach ($level5 as $item5) {
                            $level6 = User::where('sponserid', $item5->userid)->orderBy('id', 'desc')->get();
                            foreach ($level6 as $key) {
                                if ($key->package == 1) {
                                    array_push($p1, $key);
                                }
                                if ($key->package == 2) {
                                    array_push($p2, $key);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $alllevel = array($p1, $p2);
    return $alllevel;
}

function checktransactionpassword($userid, $password)
{
    $userexist = user::where('userid', $userid)->first();
    if ($userexist) {
        // $trnpass = Transaction_password::where('userid', $userid)->first();
        // if ($trnpass) {
            if (Hash::check($password, $userexist->password)) {
                return true;
            }
        // } else {
        //     return false;
        // }
    } else {
        return false;
    }
}

function usercart($userid, $event = null)
{
    return [];
}

function percentage($amount, $how)
{
    if ($amount > 0) {
        $percentvalue = ($amount / 100) * $how;
        return $percentvalue;
    }
    return 0;
}
function taxshopping($amount, $event = null)
{
    if ($amount > 0) {
        $percentvalue = ($amount / 100) * 14;
        if ($event == "totalwithtax") {
            return $amount + $percentvalue;
        }
        return $percentvalue;
    }
}
function addadminwallet($userid, $amount, $type = 'credit', $data1 = null, $data2 = null, $data3 = null)
{
    $wall = new Admin_Wallet;
    $wall->userid = $userid;
    $wall->amount = $amount;
    $wall->type = $type;
    if ($data1 != null) {
        $wall->data1 = $data1;
    }
    if ($data2 != null) {
        $wall->data2 = $data2;
    }
    if ($data3 != null) {
        $wall->data3 = $data3;
    }
    if ($wall->save()) {
        return true;
    }
    return false;
}
function upperlevelchart($userid)
{
    $data = array();
    $level0 = user::where('userid', $userid)->where('status', 1)->where('package', '>', 0)->first();
    if ($level0) {
        $level1 = user::where('userid', $level0->sponserid)->where('status', 1)->where('package', '>', 0)->first();
        if ($level1) {
            array_push($data, $level1);
            $level2 = user::where('userid', $level1->sponserid)->where('status', 1)->where('package', '>', 0)->first();
            if ($level2) {
                array_push($data, $level1);

            }
        } else {
            array_push($data, array());
        }
    }
}
function composeEmail($emailRecipient, $emailSubject, $emailBody)
{
    require base_path("vendor/autoload.php");
    $mail = new PHPMailer(true); // Passing `true` enables exceptions

    try {

        // Email server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; //  smtp host
        $mail->SMTPAuth = true;
        $mail->Username = 'donotreply@timeupindia.com'; //  sender username
        $mail->Password = 'FGH77ssd5$%^!@#$%^&*()'; // sender password
        $mail->SMTPSecure = 'ssl'; // encryption - ssl/tls
        $mail->Port = 465; // port - 587/465

        $mail->setFrom('donotreply@timeupindia.com', 'TimeUp Marketing Pvt Ltd');
        $mail->addAddress($emailRecipient);
        $mail->isHTML(true); // Set email content format to HTML

        $mail->Subject = $emailSubject;
        $mail->Body = $emailBody;

        // $mail->AltBody = plain text version of email body;

        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return true;
        }

    } catch (Exception $e) {
        return 'error Message could not be sent.';
    }
}

function userservicelist($userid)
{
    $response = array();
    if (club_user::where('userid', $userid)->where('type', 2)->first()) {
        array_push($response, [
            "name" => "2% Club Bonus",
        ]);
    }
    if (club_user::where('userid', $userid)->where('type', 3)->first()) {
        array_push($response, [
            "name" => "3% Club Bonus",
        ]);
    }
    return $response;
}

function non_worker_earned($userid, $package)
{
    $last = packages($package, 'amount') - 5;
    $amount = Transaction::where('userid', $userid)->where('category', 'extra_income')->sum('amount');
    if ($last > $amount) {
        return $amount;
    }
    return false;
}

/**
 * Add card transaction to database
 * p==3 for card transactions (p==1 for payin, p==2 for payout)
 */
function addcardtransaction($userid, $provider, $amount, $currency, $reference, $orderid, $status, $cardData = [], $responseData = [])
{
    $t = new \App\Models\CardTransaction;
    $t->userid = $userid;
    $t->reference = $reference;
    $t->orderid = $orderid;
    $t->amount = $amount;
    $t->currency = $currency;
    $t->status = $status;
    $t->provider = $provider;
    $t->card_name = $cardData['cardName'] ?? '';
    $t->card_number_masked = maskcardnumber($cardData['cardNumber'] ?? '');
    $t->card_expiry = ($cardData['expMonth'] ?? '') . '/' . ($cardData['expYear'] ?? '');
    $t->card_type = null; // Can be determined from card number if needed
    $t->transaction_type = isset($responseData['data']['link']) ? '3D' : '2D';
    $t->fees = $responseData['data']['fees'] ?? 0;
    $t->user_fees = $responseData['data']['user_fees'] ?? 0;
    $t->redirect_link = $responseData['data']['redirect_link'] ?? null;
    $t->gateway_response = json_encode($responseData);
    $t->ip_address = $cardData['ip_address'] ?? null;
    $t->callback_url = $cardData['callback_url'] ?? null;
    $t->webhook_url = $cardData['webhook_url'] ?? null;
    $t->customer_details = json_encode([
        'firstname' => $cardData['firstname'] ?? '',
        'lastname' => $cardData['lastname'] ?? '',
        'email' => $cardData['email'] ?? '',
        'phone' => $cardData['phone'] ?? '',
        'address' => $cardData['address'] ?? '',
        'city' => $cardData['city'] ?? '',
        'state' => $cardData['state'] ?? '',
        'country' => $cardData['country'] ?? '',
        'zip_code' => $cardData['zip_code'] ?? ''
    ]);
    
    if ($t->save()) {
        return $t->id;
    }
    return false;
}

/**
 * Mask card number for security (show only last 4 digits)
 */
function maskcardnumber($cardNumber)
{
    if (strlen($cardNumber) <= 4) {
        return $cardNumber;
    }
    return str_repeat('*', strlen($cardNumber) - 4) . substr($cardNumber, -4);
}

/**
 * Calculate card transaction fees based on provider
 */
function calculatecardfees($amount, $provider)
{
    $fees = 0;
    
    switch (strtolower($provider)) {
        case 'swipepointe':
            // SwipePointe fees: 2.5% + $0.30
            $fees = ($amount * 0.025) + 0.30;
            break;
        
        // Add more providers here
        // case 'provider2':
        //     $fees = ($amount * 0.03) + 0.25;
        //     break;
        
        default:
            $fees = ($amount * 0.025) + 0.30; // Default fees
            break;
    }
    
    return round($fees, 2);
}

/**
 * Get card transaction status text
 */
function getcardstatus($status)
{
    switch (strtolower($status)) {
        case 'success':
        case 'approved':
            return 'Success';
        case 'pending':
        case 'processing':
            return 'Processing';
        case 'failed':
        case 'declined':
            return 'Failed';
        case 'cancelled':
            return 'Cancelled';
        default:
            return ucfirst($status);
    }
}

/**
 * Validate card details
 */
function validatecard($cardNumber, $expMonth, $expYear, $cvv)
{
    $errors = [];
    
    // Validate card number (basic Luhn algorithm check)
    if (!isValidLuhn($cardNumber)) {
        $errors[] = 'Invalid card number';
    }
    
    // Validate expiry month
    if (!is_numeric($expMonth) || $expMonth < 1 || $expMonth > 12) {
        $errors[] = 'Invalid expiry month';
    }
    
    // Validate expiry year
    $currentYear = date('Y');
    if (!is_numeric($expYear) || $expYear < $currentYear) {
        $errors[] = 'Card has expired';
    }
    
    // Validate CVV
    if (!is_numeric($cvv) || strlen($cvv) < 3 || strlen($cvv) > 4) {
        $errors[] = 'Invalid CVV';
    }
    
    return $errors;
}

/**
 * Luhn algorithm for card number validation
 */
function isValidLuhn($number)
{
    $sum = 0;
    $numDigits = strlen($number);
    $parity = $numDigits % 2;
    
    for ($i = $numDigits - 1; $i >= 0; $i--) {
        $digit = intval($number[$i]);
        
        if ($parity == ($numDigits - $i) % 2) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        
        $sum += $digit;
    }
    
    return ($sum % 10) == 0;
}

/**
 * Get user card configuration
 */
function usercardconfig($userid, $parameter = null)
{
    $user = user::where('userid', $userid)->first();
    if (!$user) {
        return null;
    }
    
    if ($parameter) {
        return $user->{$parameter};
    }
    
    // Return all card configuration
    return [
        'card_percentage' => $user->card_percentage ?? 2.50,
        'card_fixed_fee' => $user->card_fixed_fee ?? 0.30,
        'card_callback' => $user->card_callback,
        'cardgateway' => $user->cardgateway,
        'card_ip' => $user->card_ip,
        'card_status' => $user->card_status ?? 'active'
    ];
}

/**
 * Check if user card transactions are active
 */
function isCardActive($userid)
{
    $user = user::where('userid', $userid)->first();
    if (!$user) {
        return false;
    }
    
    return $user->card_status === 'active';
}

/**
 * Get user card gateway name
 */
function getCardGatewayName($userid)
{
    $gatewayId = user('cardgateway', $userid);
    if (!$gatewayId) {
        return 'No Gateway';
    }
    
    $gateways = ALLgateway(3);
    return $gateways[$gatewayId] ?? 'Unknown Gateway';
}
