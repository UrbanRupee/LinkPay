@extends('user.layout.NewUser')

@section('css')
    <style>
        /* Re-using styles from other report/dashboard pages for consistency */
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary-text-color);
        }

        /* Form specific styles */
        .form-group label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: var(--border-radius-md);
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            /*background-color: var(--primary-color);*/
            border-color: var(--primary-color);
            border-radius: var(--border-radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            /*transition: background-color 0.2s ease, border-color 0.2s ease;*/
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .text-danger {
            color: #dc3545 !important;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .form-row > .col, .form-row > [class*="col-"] {
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1"> {{-- Centering the form on larger screens --}}
            <div class="box"> {{-- Using the .box style from the new layout --}}
                <div class="card-header">
                    <h5>{{ $title }}</h5>
                </div>
                <div class="card-body"> {{-- Using .card-body for Bootstrap 4 consistency --}}
                    <form action="#" method="POST" id="withdrawalForm">
                        @csrf {{-- Laravel CSRF token for form submission security --}}

                        {{-- Current Balance Display --}}
                        <div class="mb-4">
                            <h4 class="text-center text-muted">Your Current Main Wallet Balance:</h4>
                            <h2 class="text-center text-success font-weight-bold display-4">
                                ₹{{ number_format(wallet(user('userid'), 'int', 'payout'), 2) }}
                            </h2>
                            <p class="text-center text-info small">Minimum withdrawal amount: ₹{{ setting('min_mannual_payout') ?? 'N/A' }}</p>
                            <p class="text-center text-info small">Maximum withdrawal amount: ₹{{ setting('max_mannual_payout') ?? 'N/A' }}</p>
                        </div>

                        <hr class="mb-4">

                        {{-- Withdrawal Details --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="amount">Withdrawal Amount (₹):</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter amount" required min="0.01">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="payment_mode">Payment Mode:</label>
                                <select class="form-control" id="payment_mode" name="payment_mode" required style="padding: 0;">
                                    <option value="">Select Mode</option>
                                    <option value="bank">Bank Transfer</option>
                                    {{-- Add more modes if applicable, e.g., 'wallet', 'crypto' --}}
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name">Account Holder Name:</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Name as per bank/UPI" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="accountno">Account Number:</label>
                                <input type="text" class="form-control" id="accountno" name="accountno" placeholder="Bank Account No." required>
                            </div>
                        </div>

                        <div class="form-row" id="bank_details_row">
                            <div class="form-group col-md-6">
                                <label for="ifsc">IFSC Code (for Bank Transfer):</label>
                                <input type="text" class="form-control" id="ifsc" name="ifsc" placeholder="Enter IFSC Code">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="bank_name">Bank Name (for Bank Transfer):</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Enter Bank Name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tpassword">Transaction Password:</label>
                            <input type="password" class="form-control" id="tpassword" name="tpassword" placeholder="Enter your Transaction Password" required>
                            <small class="form-text text-muted">This is required for security.</small>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">Submit Withdrawal Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            formasync('withdrawal_amount');
            // Initial state for bank details
            toggleBankDetails();

            // Toggle bank details based on payment mode selection
            $('#payment_mode').on('change', function() {
                toggleBankDetails();
            });

            function toggleBankDetails() {
                if ($('#payment_mode').val() === 'bank') {
                    $('#bank_details_row').show();
                    $('#ifsc_code').attr('required', true);
                    $('#bank_name').attr('required', true);
                } else {
                    $('#bank_details_row').hide();
                    $('#ifsc_code').attr('required', false).val(''); // Clear and remove required
                    $('#bank_name').attr('required', false).val(''); // Clear and remove required
                }
            }

            // jQuery Validate for the form
            $("#withdrawalForm").validate({
                rules: {
                    amount: {
                        required: true,
                        number: true,
                        min: {{ setting('min_withdrawal') ?? 0.01 }}, // Use your dynamic setting for min withdrawal
                        max: {{ setting('max_withdrawal') ?? 999999999 }}, // Use your dynamic setting for max withdrawal
                        // You might also add a custom rule here to check against the user's actual wallet balance
                    },
                    payment_mode: {
                        required: true
                    },
                    account_holder_name: {
                        required: true,
                        minlength: 3
                    },
                    account_number: {
                        required: true,
                        minlength: 6 // Basic validation
                    },
                    ifsc_code: {
                        required: function(element) {
                            return $('#payment_mode').val() === 'bank';
                        },
                        minlength: 11, // IFSC codes are typically 11 alphanumeric characters
                        maxlength: 11
                    },
                    bank_name: {
                        required: function(element) {
                            return $('#payment_mode').val() === 'bank';
                        },
                        minlength: 3
                    },
                    transaction_password: {
                        required: true,
                        minlength: 4 // Adjust transaction password minimum length as per your system
                    }
                },
                messages: {
                    amount: {
                        min: "Amount must be at least ₹{0}",
                        max: "Amount cannot exceed ₹{0}"
                    }
                    // Add more custom messages as needed
                },
                errorClass: "text-danger", // Apply danger text color for errors
                validClass: "is-valid", // Add a class for valid fields (optional)
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass(errorClass).removeClass(validClass);
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass(errorClass).addClass(validClass);
                },
                submitHandler: function(form) {
                    // Show a confirmation dialog before submitting
                    Swal.fire({
                        title: 'Confirm Withdrawal?',
                        html: 'You are requesting to withdraw <strong>₹' + parseFloat($('#amount').val()).toFixed(2) + '</strong>.<br>Please ensure all details are correct.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, submit request!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // If confirmed, proceed with AJAX submission
                            // Using the `apex` function you previously had, assuming it handles AJAX requests
                            let amount = $("#amount").val();
                    let holname = $("#name").val();
                    let accno = $("#accountno").val();
                    let ifsc = $("#ifsc").val();
                    // apex("POST", "{{ url('/api/p2ptransfer') }}", new FormData(form), form,"{{ url('user/p2p-transfer') }}","#");
                    var settings = {
                      "url": "/api/payout/initiates",
                      "method": "POST",
                      "timeout": 0,
                      "headers": {
                        "Content-Type": "application/json",
                      },
                      "data": JSON.stringify({
                          "_token":"{{csrf_token()}}",
                        "token": "{{user('token')}}",
                        "userid": "{{user('userid')}}",
                        "amount": amount,
                        "mobile": "{{user('mobile')}}",
                        "name": holname,
                        "number": accno,
                        "ifsc": ifsc,
                        "ipShare":"hjbfjbheILOVErhbjeYOUrhbfPURNIMArhjbghjb",
                        "orderid": "MP{{date('Ymhid').rand(1111,9999)}}"
                      }),
                    };
                    
                    $.ajax(settings).done(function (response) {
                      console.log(response);
                        if(response.status){
                            message({status:1,title:"Success"});
                        }else{
                            message({status:0,title:response.message});
                        }
                    });
                        }
                    });
                }
            });
        });
    </script>
@endsection