<!DOCTYPE html>
<html>
<head>
    <title>Razorpay Auto Checkout</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>

<script>
    window.onload = function () {
        var options = {
            "key": "rzp_live_fNK4jam5JPmzlR", // Replace with your Razorpay Live/Test key
            "amount": "{{ $data->amount * 100 }}", // Amount in paise
            "currency": "INR",
            "name": "Xpaisa",
            "description": "A Wild Sheep Chase is the third novel by Japanese author Haruki Murakami",
            "image": "https://merchant.xpaisa.in/assets-home/images/logo/logo.jpeg",
            "order_id": "{{ $orderid }}", // Generated in backend
            
            // Limit payment methods
            "method": {
                "card": true,
                "netbanking": true,
                "wallet": false,
                "upi": false,
                "emi": false,
                "paylater": false
            },

            "handler": function (response){
                // Submit this to your server using a hidden form or AJAX
                var form = document.createElement('form');
                form.setAttribute('method', 'POST');
                form.setAttribute('action', '{{$data->callbackurl}}');

                var fields = {
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                };

                for (var field in fields) {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = field;
                    input.value = fields[field];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            },

            "prefill": {
                "name": "Gaurav Kumar",
                "email": "gaurav.kumar@example.com"
            },
            "theme": {
                "color": "#F37254"
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    }
</script>

</body>
</html>
