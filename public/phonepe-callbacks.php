<?php

$data = json_encode($_POST);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://pay.mothersolution.in/api/pg/phonepe/recharge_successfully',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>$data,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
?>

<h1>Redirecting...</h1>
    <script>
        setTimeout(()=>{
            location.href='https://pay.mothersolution.in/dashboard';
        },800);
    </script>