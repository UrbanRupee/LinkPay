<?php

$flag = false;
$sessionId = "";
// Set CASHFREE_APP_ID and CASHFREE_SECRET in .env or server environment
$api_id = getenv('CASHFREE_APP_ID') ?: '';
$secret = getenv('CASHFREE_SECRET') ?: '';
$order_id = isset($_GET['trn']) ? $_GET['trn'] : "ijhuygj5hkjbhg864544413684";
$amount = isset($_GET['am']) ? $_GET['am'] : 1;
// $amount = 1;
$user_id = isset($_GET['userid']) ? $_GET['userid'] : "1";
$name = "User";
$email = "user@gmail.com";
$mobile = isset($_GET['mob']) ? $_GET['mob'] : "1234567890";
$url = "https://api.cashfree.com/pg/orders";
$headers = array(
    "Content-Type: application/json",
    "x-api-version: 2023-08-01",
    "x-client-id: " . $api_id,
    "x-client-secret: " . $secret
);
$data = json_encode([
    'order_id' => $order_id,
    'order_amount' => $amount,
    "order_currency" => "INR",
    "customer_details" => [
        "customer_id" => "PUPNDA" . $user_id,
        "customer_name" => $name,
        "customer_phone" => $mobile,
    ],
    "order_meta" => [
        "return_url" => 'https://merchant.rudraxpay.com/cashfree/redirect.php',
        "payment_methods" => 'upi'
    ]
]);

$curl = curl_init($url);

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$resp = curl_exec($curl);

curl_close($curl);
$result = json_decode($resp);
if ($result->order_status == "ACTIVE") {
    $flag = true;
    // echo $resp;
    $sessionId = $result->payment_session_id;
//     $url1 = "https://api.cashfree.com/pg/orders/".$result->order_id;
// $headers1 = array(
//     "Content-Type: application/json",
//     "x-api-version: 2023-08-01",
//     "x-client-id: " . $api_id,
//     "x-client-secret: " . $secret
// );
// $curl1 = curl_init();

// curl_setopt_array($curl1, array(
//   CURLOPT_URL => $url1,
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'GET',
//   CURLOPT_HTTPHEADER => $headers1,
// ));

// $response = curl_exec($curl1);

// curl_close($curl1);
// echo $response;
// curl_close($curl1);
}else{
    echo $resp;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashfree Payment Gateway</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
</head>

<body>
    <p class="alert" id="paymentMessage"> </p>
    <h1>Loading..</h1>
    <!--<div class="row">-->
				<!--				<div class="col-6 bank col" bfor="phonepe">-->
				<!--					<div id="phonepe" class="icon upimount"></div>-->
				<!--					<div class="btext">PhonePe</div>-->
				<!--				</div>-->
				<!--				<div class="col-6 bank col" bfor="paytm">-->
				<!--					<div id="paytm" class="icon upimount"></div>-->
				<!--					<div class="btext">Paytm</div>-->
				<!--				</div>-->
				<!--				<div class="col-6 bank col" bfor="gpay">-->
				<!--					<div id="gpay" class="icon upimount"></div>-->
				<!--					<div class="btext">Google Pay</div>-->
				<!--				</div>-->
				<!--				<div class="col-6 bank col" bfor="default">-->
				<!--					<div id="default" class="icon upimount"></div>-->
				<!--					<div class="btext">Intent</div>-->
				<!--				</div>-->
				<!--				<div class="col-6 bank col" bfor="web">-->
				<!--					<div id="web" class="icon upimount"></div>-->
				<!--					<div class="btext">Link</div>-->
				<!--				</div>-->
				<!--				 <div class="row">-->
				<!--				<div class="col-12 bank col" bfor="qr">-->
				<!--					<div id="qr" class="icon qrmount"></div>-->
				<!--				</div>-->
								 
				<!--			</div>-->
				<!--				 <div id="cf_checkout">Show QR</div>-->
				<!--			</div>-->
<?php
if($flag){ ?>
    <script>
        const cashfree = Cashfree({
            mode:"production" //or production
        });
        let checkoutOptions = {
            paymentSessionId: "<?php echo $sessionId; ?>",
            redirectTarget: '_modal',
        }
        cashfree.checkout(checkoutOptions).then((result) => {
            if(result.error){
                // This will be true whenever user clicks on close icon inside the modal or any error happens during the payment
                console.log("User has closed the popup or there is some payment error, Check for Payment Status");
                console.log(result.error);
                window.location.href='https://merchant.rudraxpay.com/api/pg/upitel/redirect_recharge_successfully/<?php echo $order_id; ?>';
            }
            if(result.redirect){
                // This will be true when the payment redirection page couldnt be opened in the same window
                // This is an exceptional case only when the page is opened inside an inAppBrowser
                // In this case the customer will be redirected to return url once payment is completed
                console.log("Payment will be redirected");
                window.location.href='https://merchant.rudraxpay.com/api/pg/upitel/redirect_recharge_successfully/<?php echo $order_id; ?>';
            }
            if(result.paymentDetails){
                // This will be called whenever the payment is completed irrespective of transaction status
                console.log("Payment has been completed, Check for Payment Status");
                window.location.href='https://merchant.rudraxpay.com/api/pg/upitel/redirect_recharge_successfully/<?php echo $order_id; ?>';
            }
        });
//         const paymentBtn = document.getElementById("showqr");
//         const paymentMessage = document.getElementById("paymentMessage");
// 		let style = {
// 			base: {
// 				fontSize: "22px"
// 			}
// 		}
// 		const allAppNodes = document.getElementsByClassName("icon");
// 		for (let i = 0; i < allAppNodes.length; i++) {
// 			const element = allAppNodes[i];
			 
// 			let upiApp = element.getAttribute("id");
// 			let component = cashfree.create('upiApp', {
// 				values: {
// 					upiApp: upiApp,
// 				},
// 				style
// 			});
// 			component.mount("#"+upiApp);
// 			component.on("click", function(){
// 				initPay(component)
// 			});
// 			component.on("loaderror", function(data){
// 				console.log(data.error.message);
// 			});
// 			element.parentNode.addEventListener('click', function(){
// 				initPay(component)
// 			})

// 		}

//         let qr = cashfree.create("upiQr", {
// 				values: {
// 					size: "220px",
// 				}
// 			});
// 		qr.mount("#qr");
// 		qr.on('paymentrequested', function(){
// 			paymentBtn.disabled = true
// 		})
		
// 		paymentBtn.addEventListener("click", function(e){
// 			paymentMessage.innerText = "";
// 			paymentMessage.classList.remove("alert-danger");
// 			paymentMessage.classList.remove("alert-success");
// 			cashfree.pay({
// 				paymentMethod: qr,
// 				paymentSessionId: '<?php echo $sessionId; ?>',
// 				returnUrl: 'https://pushpendratechnology.com/cashfree/redirect.php',
// 			}).then(function (data) {
// 			    console.log("DataBypAYResponse",data);
// 				paymentBtn.disabled = false
// 				if(data.error) {
// 					paymentMessage.innerHTML = data.error.message;
// 					paymentMessage.classList.add("alert-danger");
// 				}
// 				if(data.paymentDetails) {
// 					paymentMessage.innerHTML = data.paymentDetails.paymentMessage;
// 					paymentMessage.classList.add("alert-success");
// 				}
// 				if(data.redirect){
// 					console.log("We are redirtion");
// 				}
// 			});
// 		});
 
// 		function initPay(comp){
// 			paymentMessage.innerText = "";
// 			paymentMessage.classList.remove("alert-danger");
// 			paymentMessage.classList.remove("alert-success");
// 			comp.disable();
// 			cashfree.pay({
// 				paymentMethod: comp,
// 				paymentSessionId: '<?php echo $sessionId; ?>',
// 				returnUrl: 'https://pushpendratechnology.com/cashfree/redirect.php',
// 				redirect: "if_required"
// 			}).then(function (data) {
// 				comp.enable();
// 				console.log(data);
// 				//data.paymentDetails -> payment message success
// 				//data.error -> payment error
// 				// data.redirect -> is redirected
// 				if(data.error) {
// 					paymentMessage.innerHTML = data.error.message;
// 					paymentMessage.classList.add("alert-danger");
// 				}
// 				if(data.paymentDetails) {
// 					paymentMessage.innerHTML = data.paymentDetails.paymentMessage;
// 					paymentMessage.classList.add("alert-success");
// 				}
// 				if(data.redirect){
// 					console.log("We are redirtion");
// 				}
// 			});
// 		}

    </script>
<?php } ?>
</body>

</html>