@extends('frontend.layout.design1')

@section('css')
 <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
@endsection

@section('content')
    <main class="main-wrapper">

        <!-- Start Cart Area  -->
        <div class="axil-product-cart-area axil-section-gap">
            <div class="container">
                <div class="axil-product-cart-wrap">
                    <div class="product-table-heading">
                        <h4 class="title">Payment Gateway</h4>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="axil-order-summery mt--80">
                                <h5 class="title mb--20">Payment Summary</h5>
                                <div class="summery-table-wrap">
                                    <table class="table summery-table mb--30">
                                        <tbody>
                                            <style>
                                                #qrCodeContainer img{
                                                    margin:auto;
                                                }
                                            </style>
                                            <tr class="order-subtotal">
                                                <td colspan="2">
                                                    <div id="qrCodeContainer"></div>
                                                </td>
                                            </tr>
                                            <tr class="order-subtotal">
                                                <td>UPI Id</td>
                                                <td>{{ setting('upi_id') }}</td>
                                            </tr>
                                            <tr class="order-subtotal">
                                                <td>Subtotal</td>
                                                <td>₹ {{ number_format($data->amount, 2) }}</td>
                                            </tr>
                                            <!--<tr class="order-shipping">-->
                                            <!--    <td>Payment Gateway</td>-->
                                            <!--    <td>-->
                                            <!--        <div class="input-group">-->
                                            <!--            <input type="radio" id="method1" name="method" checked>-->
                                            <!--            <label for="method1">Online Payment</label>-->
                                            <!--        </div>-->
                                            <!--        <div class="input-group">-->
                                            <!--            <input type="radio" id="method2" name="method" checked>-->
                                            <!--            <label for="method2">Mannual Bank Payment</label>-->
                                            <!--        </div>-->
                                            <!--    </td>-->
                                            <!--</tr>-->
                                            <tr class="order-tax">
                                                <td>State Tax</td>
                                                <td>₹ {{ number_format($data->tax, 2) }}</td>
                                            </tr>
                                            <tr class="order-total">
                                                <td>Total</td>
                                                <td class="order-total-amount">
                                                    {{ number_format($data->tax + $data->amount, 2) }}</td>
                                            </tr>
                                            <tr class="order-total">
                                                <td>Enter UTR No.</td>
                                                <td class="order-total-amount">
                                                    <div class="input-group">
                                                        <input type="text" id="utr" name="utr" required placeholder="Enter UTR No." onchange="updateVariable()">
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="javascript:void(0);" id="rzp-button1"
                                    class="axil-btn btn-bg-primary checkout-btn">Process to Payment</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Cart Area  -->

    </main>
@endsection

@section('js')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
  // Replace these variables with your actual data
  var upiId = '{{ setting('upi_id') }}';
  var amount = '{{$data->tax + $data->amount}}';
  var message = 'Payment for Order XYZ';

  function drawQRCode() {
    var qrContent = `upi://pay?pa=${upiId}&am=${amount}&pn=&tn=${message}`;

    var qrCode = new QRCode(document.getElementById('qrCodeContainer'), {
      text: qrContent,
      width: 200,
      height: 200,
    });
  }

  drawQRCode();
</script>

    <script>
    let utrno = '';
    function updateVariable() {
  const inputElement = document.getElementById('utr');
  utrno = inputElement.value;
  console.log('Input value:', utrno);
}
        var options = {
            "key": "rzp_test_IRvvkXe9jII3H8", // Enter the Key ID generated from the Dashboard
            "amount": "{{ $data->amount * 100 }}", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
            "currency": "INR",
            "name": "{{ user('name') }}",
            "description": "TimeUp Payment Request",
            "image": "/assets/images/logo/logo.png",
            "order_id": "order_{{ $data->transaction_id }}", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
            "handler": function(response) {
                alert(response.razorpay_payment_id);
                alert(response.razorpay_order_id);
                alert(response.razorpay_signature)
            },
            "prefill": {
                "name": "{{ user('name') }}",
                "email": "{{ user('email') }}",
                "contact": "{{ user('mobile') }}"
            },
            "notes": {
                "address": "{{ user('address') }}"
            },
            "theme": {
                "color": "#3399cc"
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function(response) {
            alert(response.error.code);
            alert(response.error.description);
            alert(response.error.source);
            alert(response.error.step);
            alert(response.error.reason);
            alert(response.error.metadata.order_id);
            alert(response.error.metadata.payment_id);
        });
        document.getElementById('rzp-button1').onclick = function(e) {
            if ($("#method1").is(":checked")) {
                // rzp1.open();
                // e.preventDefault();
                alert("Currently unavailable");
            }else{
                if(utrno == ''){
                    alert("Please enter UTR No.");
                }else{
                window.location.href="/submit-utr/"+utrno+"/{{$data->tax + $data->amount}}";
                }
            }
        }
    </script>
@endsection
