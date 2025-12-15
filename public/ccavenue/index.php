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
<form name="frmPayment" action="ccavRequestHandler.php" method="POST">
    <input type="hidden" name="merchant_id" value="<?php echo CCA_MERCHANT_ID; ?>"> 
    <input type="hidden" name="language" value="EN"> 
    <input type="hidden" name="payment_option" value="OPTUPI"> 
    <input type="hidden" name="instant_gratification" value="Y"> 
    <input type="hidden" name="card_name" value="UPI"> 
    <input type="hidden" name="card_type" value="UPI"> 
    <input type="hidden" name="dataAcceptedAt" value="CCAvenue"> 
    <input type="hidden" name="data_accept" value="N"> 
    <input type="hidden" name="upiPaymentFlag" value="Intent"> 
    <input type="hidden" name="status" value="ACTI">
    <input type="hidden" name="amount" value="100">
    <input type="hidden" name="currency" value="INR"> 
    <!--<input type="hidden" name="virtualAddress" value="motherpay@axl"> -->
    <input type="hidden" name="redirect_url" value="https://pushpendratechnology.com/ccavenue/res.php"> 
    <input type="hidden" name="cancel_url" value="https://pushpendratechnology.com/ccavenue/payment-cancel.php"> 
    
    <div>
    <input type="text" name="delivery_name" value="Adarsh Padney" class="form-field" Placeholder="Billing Name"> 
    <input type="text" name="billing_city" value="Noida" class="form-field" Placeholder="Billing Address">
    <input type="text" name="delivery_city" value="Noida" class="form-field" Placeholder="Billing Address">
    <input type="text" name="delivery_address" value="Noida" class="form-field" Placeholder="Billing Address">
    <input type="text" name="billing_address" value="Noida" class="form-field" Placeholder="Billing Address">
    <input type="text" name="delivery_tel" value="1234567890" class="form-field" Placeholder="Billing Address">
    </div>
    <div>
    <input type="text" name="billing_state" value="Uttar pradesh" class="form-field" Placeholder="State"> 
    <input type="text" name="delivery_state" value="Uttar pradesh" class="form-field" Placeholder="State"> 
    <input type="text" name="delivery_zip" value="201204" class="form-field" Placeholder="Zipcode">
    <input type="text" name="billing_zip" value="201204" class="form-field" Placeholder="Zipcode">
    </div>
    <div>
    <input type="text" name="billing_country" value="India" class="form-field" Placeholder="Country">
    <input type="text" name="delivery_country" value="India" class="form-field" Placeholder="Country">
    <input type="text" name="billing_tel" value="8957287400" class="form-field" Placeholder="Phone">
    </div> 
    <div>
    <input type="text" name="billing_email" value="adarsh@pushpendratechnology.com" class="form-field" Placeholder="Email">
    </div>
    <div>
    <button class="btn-payment" type="submit">Pay Now</button>
    </div>
</form>
</div>