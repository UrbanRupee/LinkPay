<?php

function gatewayInitiate($amount,$orderid)
    {
        $merchantId = 'M22MA4PAMRRA2'; // sandbox or test merchantId
        $apiKey="b5ac135e-cce1-4be0-8f87-6a6dc72e9971"; // sandbox or test APIKEY
        $redirectUrl = "https://mothersolution.in/phonepe-callbacks.php";
        $name="MS User";
        $email="info@mothersolution.in";
        $mobile=8957287400;
        $description = 'Purchase or Maintain Services';
        $order_id = $orderid;
        $paymentData = array(
            'merchantId' => $merchantId,
            'merchantTransactionId' => $order_id, // test transactionID
            "merchantUserId"=>"MUID123",
            'amount' => $amount*100,
            'redirectUrl'=>$redirectUrl,
            'redirectMode'=>"POST",
            'callbackUrl'=>$redirectUrl,
            "merchantOrderId"=>$order_id,
           "mobileNumber"=>$mobile,
           "message"=>$description,
           "email"=>$email,
           "shortName"=>$name,
           "paymentInstrument"=> array(    
            "type"=> "PAY_PAGE",
          )
        );
        $jsonencode = json_encode($paymentData);
        $payloadMain = base64_encode($jsonencode);
        $salt_index = 1; //key index 1
        $payload = $payloadMain . "/pg/v1/pay" . $apiKey;
        $sha256 = hash("sha256", $payload);
        $final_x_header = $sha256 . '###' . $salt_index;
        $request = json_encode(array('request'=>$payloadMain));
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
             "Origin: https://mothersolution.in"
          ],
        ]);
         
        $response = curl_exec($curl);
        $err = curl_error($curl);
         
        curl_close($curl);
         
        if ($err) {
          return "cURL Error #:" . $err;
        } else {
            return json_decode($response);
        }
    }
    
    $res = gatewayInitiate($_GET['amount'],$_GET['orderid']);
    $url = $res->data->instrumentResponse->redirectInfo->url;
    // header("Location: $url");
    ?>
    <h1>Loading...</h1>
    <script>
        setTimeout(()=>{
            location.href='<?php echo $url; ?>';
        },800);
    </script>
    
    
    
    