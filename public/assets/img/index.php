<h4>QR Pay Collection with UTR</h4>
<form action="qrview.php" method="post">
  <input type="text" name="upi" id="upi" autocomplete="off" value="" placeholder="Enter Your UPI ID">
  <input type="text" name="amt"  autocomplete="off" value="" placeholder="Enter Amount">
 <input type="hidden" name="token"  value="<?php echo base64_encode("9999999999"); ?>">   
  <input type="submit" name="qrpay" value="Generate">
</form>

