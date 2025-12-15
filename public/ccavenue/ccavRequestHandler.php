<html>
<head>
<title> CCAvenue Payment Gateway Integration kit</title>
</head>
<body>
<center>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('Crypto.php');
require_once "config.php";
$working_key = CCA_WORKING_KEY;
$access_code = CCA_ACCESS_CODE;
$orderid = date("YmdHis");
$merchant_data='merchant_id='.CCA_MERCHANT_ID.'&billing_address=Room no 1101, near Railway station Ambad&billing_email=chandrakant.patil@avenues.info&delivery_state=Andhra&card_name=UPI&order_id='.$orderid.'&redirect_url=&delivery_name=Chaplin&mobile_no=9595226054&billing_state=MP&currency=INR&payment_option=OPTUPI&billing_country=India&card_type=UPI&delivery_city=Hyderabad&delivery_address=room no.701 near bus&billing_city=Indore&billing_notes=order will be shipped&cancel_url=&billing_tel=9595226054&billing_zip=425001&delivery_country=India&amount=1&billing_name=Test&delivery_tel=9595226054&delivery_zip=425001
&upiPaymentFlag=Intent&billing_city=Indore&billing_notes=order will be shipped&cancel_url=&billing_tel=9595226054&billing_zip=425001&delivery_country=India&amount=1&billing_name=Test&delivery_tel=9595226054&delivery_zip=425001';
echo $merchant_data;
$encrypted_data=encrypt($merchant_data,$working_key); 
// $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction',
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'POST',
//   CURLOPT_POSTFIELDS => array('encRequest' => $encrypted_data,'access_code' => $access_code),
//   CURLOPT_HTTPHEADER => array(
//     'Content-Type: application/json'
//   ),
// ));

// $response = curl_exec($curl);

// curl_close($curl);
// echo $response;
?>
<form method="post" name="redirect" action="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction" >
<?php
echo "<input type=hidden name=encRequest value=$encrypted_data>";
echo "<input type=hidden name=access_code value=$access_code>";
?>
</form>
</center>
<script language='javascript'>document.redirect.submit();</script>
</body>
</html>