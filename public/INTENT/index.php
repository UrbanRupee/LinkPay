<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$MERCHANTID="M22MA4PAMRRA2";
$SALTKEY="b5ac135e-cce1-4be0-8f87-6a6dc72e9971";
$SALTINDEX="1";
$env="PRODUCTION";
$SHOULDPUBLISHEVENTS=true;

$phonePePaymentsClient = new PhonePePaymentClient($MERCHANTID, $SALTKEY, $SALTINDEX, $env, $SHOLDPUBLISHEVENTS);

$merchantTransactionId = 'PHPSDK' . date("ymdHis") . "upiIntentPayTest";
$request = PgPayRequestBuilder::builder()
    ->mobileNumber("9090909090")
    ->callbackUrl("https://webhook.in/test")
    ->merchantId($MERCHANTID)
    ->merchantUserId("855555")
    ->amount(200)
    ->deviceContext("Constants::IOS")
    ->merchantTransactionId($merchantTransactionId)
    ->paymentInstrument(
        InstrumentBuilder::getUpiIntentInstrumentBuilder()
            ->targetApp("com.phonepe.com")
            ->build()
    )
    ->build();

$response = $phonePePaymentsClient->pay($request);
$intentUrl = $response->getInstrumentResponse()->getIntentUrl();