@extends('frontend.layout.design1')

@section('css')
@endsection

@section('content')
    <main class="main-wrapper">

        <!-- Start Cart Area  -->
        <div class="axil-product-cart-area axil-section-gap">
            <div class="container">
                <div class="axil-product-cart-wrap">
                    <div class="product-table-heading">
                        <h4 class="title">Company Bank Details</h4>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="axil-order-summery mt--80">
                                <h5 class="title mb--20">Details</h5>
                                <div class="summery-table-wrap">
                                    <table class="table summery-table mb--30">
                                        <tbody>
                                            <tr>
                                                <td>Bank name</td>
                                                <td>{{ setting('bank_name') }}</td>
                                            </tr>
                                            <tr>
                                                <td>Account No.</td>
                                                <td>{{ setting('account_no') }}</td>
                                            </tr>
                                            <tr>
                                                <td>IFSC Code</td>
                                                <td>{{ setting('ifsc') }}</td>
                                            </tr>
                                            <tr>
                                                <td>Account Holder Name</td>
                                                <td>{{ setting('name') }}</td>
                                            </tr>
                                            <tr>
                                                <td>Branch</td>
                                                <td>{{ setting('branch') }}</td>
                                            </tr>
                                            <tr>
                                                <td>UPI Id</td>
                                                <td>{{ setting('upi_id') }}</td>
                                            </tr>
                                            {{-- @if (setting('qr_code') != '')
                                                <tr>
                                                    <td>QR Code</td>
                                                    <td><img src="{{ setting('qr_code') }}" alt="Upi QR Code Timeup!"></td>
                                                </tr>
                                            @endif --}}
                                        </tbody>
                                        <p>Always share payment proof else your payment not valid. <br>
                                        <a href="mailto:info@timeupindia.com">Our Official mail id :- info@timeupindia.com</a>
                                        </p>
                                    </table>
                                </div>
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
@endsection
