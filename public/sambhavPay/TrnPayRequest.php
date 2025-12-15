<?php

require 'SambhavPayConfig.php';
if (isset($_POST) && !empty($_POST)) {
    $error = '';
    $Sambhavpay = new SambhavPay;
    if (isset($_POST['mid']) && !empty($_POST['mid'])) {
        $Sambhavpay->setMid($_POST['mid']);
    } else {
        $error = $error . 'Mid Field is required.<br/>';
    }
    if (isset($_POST['secretKey']) && !empty($_POST['secretKey'])) {
        $Sambhavpay->setSecretKey($_POST['secretKey']);
    } else {
        $error = $error . 'SecretKey Field is required.<br/>';
    }
    if (isset($_POST['saltKey']) && !empty($_POST['saltKey'])) {
        $Sambhavpay->setSaltKey($_POST['saltKey']);
    } else {
        $error = $error . 'SaltKey Field is required.<br/>';
    }
    if (isset($_POST['orderNo']) && !empty($_POST['orderNo'])) {
        $Sambhavpay->setOrderNo($_POST['orderNo']);
    } else {
        $error = $error . 'OrderNo. Field is required.<br/>';
    }
    if (isset($_POST['amount']) && !empty($_POST['amount'])) {
        $Sambhavpay->setAmount($_POST['amount']);
    } else {
        $error = $error . 'Amount Field is required.<br/>';
    }
    if (isset($_POST['currency']) && !empty($_POST['currency'])) {
        $Sambhavpay->setCurrency($_POST['currency']);
    } else {
        $error = $error . 'Currency Field is required.<br/>';
    }
    if (isset($_POST['txnReqType']) && !empty($_POST['txnReqType'])) {
        $Sambhavpay->setTxnReqType($_POST['txnReqType']);
    } else {
        $error = $error . 'TxnReqType Field is required.<br/>';
    }

    $Sambhavpay->setUndefinedField1($_POST['undefinedField1']);
    $Sambhavpay->setUndefinedField2($_POST['undefinedField2']);
    $Sambhavpay->setUndefinedField3($_POST['undefinedField3']);
    $Sambhavpay->setUndefinedField4($_POST['undefinedField4']);
    $Sambhavpay->setUndefinedField5($_POST['undefinedField5']);
    $Sambhavpay->setUndefinedField6($_POST['undefinedField6']);
    $Sambhavpay->setUndefinedField7($_POST['undefinedField7']);
    $Sambhavpay->setUndefinedField8($_POST['undefinedField8']);
    $Sambhavpay->setUndefinedField9($_POST['undefinedField9']);
    $Sambhavpay->setUndefinedField10($_POST['undefinedField10']);


    if (isset($_POST['emailId']) && !empty($_POST['emailId'])) {
        $Sambhavpay->setEmailId($_POST['emailId']);
    } else {
        $error = $error . 'EmailId Field is required.<br/>';
    }
    if (isset($_POST['mobileNo']) && !empty($_POST['mobileNo'])) {
        $Sambhavpay->setMobileNo($_POST['mobileNo']);
    } else {
        $error = $error . 'MobileNo Field is required.<br/>';
    }

    $Sambhavpay->setAddress($_POST['address']);
    $Sambhavpay->setCity($_POST['city']);
    $Sambhavpay->setState($_POST['state']);
    $Sambhavpay->setPincode($_POST['pincode']);

    if (isset($_POST['transactionMethod']) && !empty($_POST['transactionMethod'])) {
        $Sambhavpay->setTransactionMethod($_POST['transactionMethod']);
    } else {
        $error = $error . 'TransactionMethod Field is required.<br/>';
    }

    $Sambhavpay->setBankCode($_POST['bankCode']);
    $Sambhavpay->setVPA($_POST['vpa']);
    $Sambhavpay->setCardNumber($_POST['cardNumber']);
    $Sambhavpay->setExpiryDate($_POST['expiryDate']);
    $Sambhavpay->setCVV($_POST['cvv']);

    if (isset($_POST['customerName']) && !empty($_POST['customerName'])) {
        $Sambhavpay->setCustomerName($_POST['customerName']);
    } else {
        $error = $error . 'CustomerName Field is required.<br/>';
    }

    $Sambhavpay->setRespUrl($_POST['respUrl']);

    if (isset($_POST['optional1']) && !empty($_POST['optional1'])) {
        $Sambhavpay->setOptional1($_POST['optional1']);
    } else {
        $error = $error . 'UPIType/ Optional1 Field is required.<br/>';
    }



    if (empty($error)) {
        $json = $Sambhavpay->doPayment();
        respHandler(json_decode($json));
    } else {
        echo $error;
    }
} else {
    echo 'oops, some error!';
}

function respHandler($jsonData){

    $mid = "900000000000008";
    $secretKey = "scrh0e0TZiA6J6bKXvJs5Pme8CMavx0cNmi";
    $saltKey = "sal9XIXl94aP3ZC6ZFIki32ugGXBVJBfr";
    
    $Sambhavpay = new SambhavPay;
    
    $Sambhavpay->setMid($mid);
    $Sambhavpay->setSecretKey($secretKey);
    $Sambhavpay->setSaltKey($saltKey);

    $data = json_decode($jsonData->data);
    $respData = $data->respData;
    $mid = $data->mid;
    $checkSum = $data->checkSum;
    
    $response = $Sambhavpay->getResponse($respData,$mid,$checkSum);
    printResponse($response);
}

function printResponse($res){
    $resTxnObj = json_decode($res);
    echo $resTxnObj->upiString;
}