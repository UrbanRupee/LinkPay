<?php include('config.php');?>
<?php include('Crypto.php');?>
<?php
error_reporting ( 0 );

$workingKey = CCA_WORKING_KEY; // Working Key should be provided here.
$encResponse = $_POST ["encResp"]; // This is the response sent by the CCAvenue Server
$rcvdString = decrypt ( $encResponse, $workingKey ); // Crypto Decryption used as per the specified working key.
$order_status = "";
$decryptValues = explode ( '&', $rcvdString );
$dataSize = sizeof ( $decryptValues );
for($i = 0; $i < $dataSize; $i ++) {
	$information = explode ( '=', $decryptValues[$i]);
	$responseMap[$information[0]] = $information[1];
}
$order_status = json_encode($responseMap);
$con = mysqli_connect("localhost", "u492557440_pushpendratech", "p+3cpmt6LO", "u492557440_pushpendratech");
$query = "INSERT INTO `log` (`data`) VALUES ('$order_status')";
mysqli_query($con,$query);