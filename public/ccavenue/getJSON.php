<?php 
require_once "config.php";
?>
<style>
body {
    max-width: 620px;
    margin: 20px auto;
    font-size: 0.95em;
    font-family: Arial;
}
.form-field {
    padding: 10px;
    width: 250px;
    border: #c1c0c0 1px solid;
    border-radius: 3px;
    margin: 0px 20px 20px 0px;
    background-color: white;
}
#ccav-payment-form {
    border: #c1c0c0 1px solid;
    padding: 30px;
}
.btn-payment {
    background: #009614;
    border: #038214 1px solid;
    padding: 8px 30px;
    border-radius: 3px;
    color: #FFF;
    cursor: pointer;
}
</style>
<h1>CCAvenue Payment Gateway Intgration</h1>
<div id="ccav-payment-form">
<form name="frmPayment" action="https://secure.ccavenue.com/transaction/transaction.do" method="GET">
    <input type="hidden" name="merchant_id" value="<?php echo CCA_MERCHANT_ID; ?>"> 
    <input type="hidden" name="access_code" value="<?php echo CCA_ACCESS_CODE; ?>">
    <input type="hidden" name="command" value="getJsonData">
    <input type="text" name="amount" value="1">
    <input type="hidden" name="currency" value="INR">
    </div>
    <div>
    <button class="btn-payment" type="submit">Pay Now</button>
    </div>
</form>
</div>