<?php include('Crypto.php');?>
<?php require_once "config.php"; ?>
<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$workingKey = CCA_WORKING_KEY; // Working Key should be provided here.
$encResponse = $_POST["encResp"]; // This is the response sent by the CCAvenue Server
$rcvdString = decrypt( $encResponse, $workingKey ); // Crypto Decryption used as per the specified working key.
$order_status = "";
$decryptValues = explode( '&', $rcvdString );
$dataSize = sizeof( $decryptValues );
for($i = 0; $i < $dataSize; $i ++) {
	$information = explode( '=', $decryptValues[$i] );
	$responseMap[$information [0]] = $information[1];
}


echo json_encode($responseMap);