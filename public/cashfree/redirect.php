<script>
    <?php 
    $txnid = $_GET['site'];
    if(isset($txnid) && ( $txnid == "true" || $txnid == true)){ ?>
        window.location.href = "https://openmart.live";
    <?php }else{ ?>
        window.location.href = "https://openmart.live";
    <?php }
    ?>
</script>