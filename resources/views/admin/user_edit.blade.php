@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{$title}}</h6>
                        {{$user->userid}}
                        <form class="forms-sample" id="edit_profile">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Name:</label>
                                    <input class="form-control" value="{{ $user->name }}" name="name">
                                </div>
                            </div>
                            @if($role == "admin")
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Aadhar No.:</label>
                                    <input class="form-control mb-4 mb-md-0" name="aadhar_card" id="aadhar_card" value="{{$user->aadhar_card}}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pan No.:</label>
                                    <input class="form-control mb-4 mb-md-0" name="pan_card" id="pan_card" value="{{$user->pan_card}}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Mobile:</label>
                                    <input type="number" class="form-control" value="{{ $user->mobile }}" name="mobile">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Email ID:</label>
                                    <input type="email" class="form-control mb-4 mb-md-0" value="{{ $user->email }}" name="email">
                                </div>
                            </div>
                            @endif
                            <h5>Payin</h5>
                            <hr>
                            @php
                                $defaultPayinPercentage = $user->percentage !== null
                                    ? rtrim(rtrim(number_format((float) $user->percentage, 2, '.', ''), '0'), '.')
                                    : null;
                                $defaultPlaceholder = $defaultPayinPercentage !== null
                                    ? "Default {$defaultPayinPercentage}%"
                                    : 'Falls back to default pay-in %';
                            @endphp
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Percentage:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->percentage}}" name="percentage" id="percentage">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Callback:</label>
                                    <input type="text" class="form-control" value="{{$user->callback}}" name="callback" id="callback">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Success Redirect URL:</label>
                                    <input type="text" class="form-control" value="{{$user->payin_success_redirect}}" name="payin_success_redirect" id="payin_success_redirect" placeholder="https://yourdomain.com/payin/success">
                                    <small class="text-muted d-block mt-1">Optional: overrides default payment success page.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gateway:</label>
                                    <select class="form-control" value="{{$user->payingateway}}" name="payingateway" id="payingateway">
                                        <option value>Select Payin Gateway</option>
                                        @foreach(ALLgateway(1) as $index => $name)
                                            <option value="{{ $index }}" {{$index==$user->payingateway?"selected":""}}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h6 class="mb-1">Mode-wise Commercial Overrides</h6>
                                        <span class="badge bg-light text-dark">
                                            Default fallback: <strong>{{ $defaultPayinPercentage ?? __('not set') }}%</strong>
                                        </span>
                                    </div>
                                    <p class="text-muted mb-3">Configure different commissions for each payment mode. Empty fields will inherit the default pay-in percentage.</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-qrcode text-primary"></i> UPI %
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">UPI</span>
                                        <input type="text" class="form-control" value="{{ $user->upi_percentage }}" name="upi_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Collects for intent & QR transactions.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-credit-card text-danger"></i> Credit Card %
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_percentage }}" name="cc_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Default for all credit cards (fallback).</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-money-check text-success"></i> Debit Card %
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_percentage }}" name="dc_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Default for all debit cards (fallback).</small>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3">Credit Card Type-Specific</h6>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CC - Master %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC-M</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_master_percentage }}" name="cc_master_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Mastercard credit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CC - Visa %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC-V</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_visa_percentage }}" name="cc_visa_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Visa credit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CC - RuPay %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC-R</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_rupay_percentage }}" name="cc_rupay_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">RuPay credit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CC - Maestro %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC-Ma</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_maestro_percentage }}" name="cc_maestro_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Maestro credit cards.</small>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">CC - Amex %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC-A</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_amex_percentage }}" name="cc_amex_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">American Express credit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CC - Diners %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC-D</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_diners_percentage }}" name="cc_diners_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Diners Club credit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CC - Others %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">CC-O</span>
                                        <input type="text" class="form-control" value="{{ $user->cc_others_percentage }}" name="cc_others_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Other credit card types.</small>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="mb-3">Debit Card Type-Specific</h6>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">DC - Master %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC-M</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_master_percentage }}" name="dc_master_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Mastercard debit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">DC - Visa %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC-V</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_visa_percentage }}" name="dc_visa_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Visa debit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">DC - RuPay %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC-R</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_rupay_percentage }}" name="dc_rupay_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">RuPay debit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">DC - Maestro %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC-Ma</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_maestro_percentage }}" name="dc_maestro_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Maestro debit cards.</small>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">DC - Amex %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC-A</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_amex_percentage }}" name="dc_amex_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">American Express debit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">DC - Diners %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC-D</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_diners_percentage }}" name="dc_diners_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Diners Club debit cards.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">DC - Others %</label>
                                    <div class="input-group">
                                        <span class="input-group-text">DC-O</span>
                                        <input type="text" class="form-control" value="{{ $user->dc_others_percentage }}" name="dc_others_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">Other debit card types.</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-university text-warning"></i> Netbanking %
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">NB</span>
                                        <input type="text" class="form-control" value="{{ $user->nb_percentage }}" name="nb_percentage" placeholder="{{ $defaultPlaceholder }}">
                                    </div>
                                    <small class="text-muted">IMPS / RTGS / bank logins.</small>
                                </div>
                            </div>
                            <h5>PayOut</h5>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">Percentage:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->out_percentage}}" name="out_percentage" id="out_percentage">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Callback:</label>
                                    <input type="text" class="form-control" value="{{$user->out_callback}}" name="out_callback" id="out_callback">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">IP:</label>
                                    <input type="text" class="form-control" value="{{$user->out_ip}}" name="out_ip" id="out_ip">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Gateway:</label>
                                    <select class="form-control" value="{{$user->payoutgateway}}" name="payoutgateway" id="payoutgateway">
                                        <option value>Select Payout Gateway</option>
                                        @foreach(ALLgateway(2) as $index => $name)
                                            <option value="{{ $index }}" {{$index==$user->payoutgateway?"selected":""}}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Secure Token:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->token}}" name="token" id="token" readonly>
                                </div>
                            </div>
                            {{-- REMOVED: Card Transactions section --}}
                            {{-- <h5>Card Transactions</h5>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Percentage:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->card_percentage ?? ''}}" name="card_percentage" id="card_percentage" placeholder="2.5">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fixed Fee:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->card_fixed_fee ?? ''}}" name="card_fixed_fee" id="card_fixed_fee" placeholder="0.30">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Callback:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->card_callback ?? ''}}" name="card_callback" id="card_callback" placeholder="https://yourdomain.com/card/callback">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gateway:</label>
                                    <select class="form-control" value="{{$user->cardgateway ?? ''}}" name="cardgateway" id="cardgateway">
                                        <option value>Select Card Gateway</option>
                                        @foreach(ALLgateway(3) as $index => $name)
                                            <option value="{{ $index }}" {{$index==($user->cardgateway ?? '')?"selected":""}}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Card Processing Fee:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->card_processing_fee ?? ''}}" id="card_processing_fee" placeholder="2.5" readonly>
                                    <small class="form-text text-muted">Percentage + Fixed Fee (Calculated)</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Card IP Whitelist:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->card_ip ?? ''}}" name="card_ip" id="card_ip" placeholder="127.0.0.1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Card Status:</label>
                                    <select class="form-control" value="{{$user->card_status ?? 'active'}}" name="card_status" id="card_status">
                                        <option value="active" {{($user->card_status ?? 'active')=='active'?"selected":""}}>Active</option>
                                        <option value="inactive" {{($user->card_status ?? 'active')=='inactive'?"selected":""}}>Inactive</option>
                                        <option value="hold" {{($user->card_status ?? 'active')=='hold'?"selected":""}}>Hold</option>
                                    </select>
                                </div>
                            </div> --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{bank($user->userid,'bank_name')}}" name="bank_name" id="bank_name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account No:</label>
                                    <input type="number" class="form-control" value="{{bank($user->userid,'account_no')}}" name="account_no" id="account_no">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">IFSC Code:</label>
                                    <input type="text" class="form-control" value="{{bank($user->userid,'ifsc_code')}}" name="ifsc_code" id="ifsc_code">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary">Update now</button>
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
    $("#edit_profile").validate({
        submitHandler: function(form){
            // Disable submit button
            var submitBtn = $(form).find('button[type=submit]');
            submitBtn.prop('disabled', true).html('Updating...');

            // Make AJAX request
            $.ajax({
                type: 'POST',
                url: "{{ url('/admin/api/user_edit/'.$id) }}",
                data: new FormData(form),
                contentType: false,
                cache: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    if (response.status == 1) {
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Redirect to user list
                            window.location.href = '/admin/userlist';
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error'
                        });
                        submitBtn.prop('disabled', false).html('Update now');
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Update failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                    submitBtn.prop('disabled', false).html('Update now');
                }
            });
        }
    });

    // Card processing fee calculation
    function calculateCardFee() {
        const percentage = parseFloat($('#card_percentage').val()) || 0;
        const fixedFee = parseFloat($('#card_fixed_fee').val()) || 0;
        const processingFee = percentage + fixedFee;
        $('#card_processing_fee').val(processingFee.toFixed(2));
    }

    // Calculate fee when percentage or fixed fee changes
    $('#card_percentage, #card_fixed_fee').on('input', function() {
        calculateCardFee();
    });

    // Initialize card fee calculation on page load
    $(document).ready(function() {
        calculateCardFee();
    });
</script>
@endsection

                                        @foreach(ALLgateway(3) as $index => $name)
                                            <option value="{{ $index }}" {{$index==($user->cardgateway ?? '')?"selected":""}}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Card Processing Fee:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->card_processing_fee ?? ''}}" id="card_processing_fee" placeholder="2.5" readonly>
                                    <small class="form-text text-muted">Percentage + Fixed Fee (Calculated)</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Card IP Whitelist:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{$user->card_ip ?? ''}}" name="card_ip" id="card_ip" placeholder="127.0.0.1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Card Status:</label>
                                    <select class="form-control" value="{{$user->card_status ?? 'active'}}" name="card_status" id="card_status">
                                        <option value="active" {{($user->card_status ?? 'active')=='active'?"selected":""}}>Active</option>
                                        <option value="inactive" {{($user->card_status ?? 'active')=='inactive'?"selected":""}}>Inactive</option>
                                        <option value="hold" {{($user->card_status ?? 'active')=='hold'?"selected":""}}>Hold</option>
                                    </select>
                                </div>
                            </div> --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bank Name:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{bank($user->userid,'bank_name')}}" name="bank_name" id="bank_name">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Account No:</label>
                                    <input type="number" class="form-control" value="{{bank($user->userid,'account_no')}}" name="account_no" id="account_no">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">IFSC Code:</label>
                                    <input type="text" class="form-control" value="{{bank($user->userid,'ifsc_code')}}" name="ifsc_code" id="ifsc_code">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary">Update now</button>
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
    $("#edit_profile").validate({
        submitHandler: function(form){
            // Disable submit button
            var submitBtn = $(form).find('button[type=submit]');
            submitBtn.prop('disabled', true).html('Updating...');

            // Make AJAX request
            $.ajax({
                type: 'POST',
                url: "{{ url('/admin/api/user_edit/'.$id) }}",
                data: new FormData(form),
                contentType: false,
                cache: false,
                processData: false,
                dataType: "json",
                success: function(response) {
                    if (response.status == 1) {
                        // Show success message
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Redirect to user list
                            window.location.href = '/admin/userlist';
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error'
                        });
                        submitBtn.prop('disabled', false).html('Update now');
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Update failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                    submitBtn.prop('disabled', false).html('Update now');
                }
            });
        }
    });

    // Card processing fee calculation
    function calculateCardFee() {
        const percentage = parseFloat($('#card_percentage').val()) || 0;
        const fixedFee = parseFloat($('#card_fixed_fee').val()) || 0;
        const processingFee = percentage + fixedFee;
        $('#card_processing_fee').val(processingFee.toFixed(2));
    }

    // Calculate fee when percentage or fixed fee changes
    $('#card_percentage, #card_fixed_fee').on('input', function() {
        calculateCardFee();
    });

    // Initialize card fee calculation on page load
    $(document).ready(function() {
        calculateCardFee();
    });
</script>
@endsection
