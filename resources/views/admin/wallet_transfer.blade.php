@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Category</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Wallet Transfer (Between Wallets)
                        </h6>
                        <p class="text-muted">Transfer amount from one wallet to another for the same user</p>

                        <form class="forms-sample" id="amounttransferswitcher">
                            @csrf
                            <div class="mb-3">
                                <label for="userids" class="form-label">Select User</label>
                                <select class="js-example-basic-single form-select" name="userid" id="userids"
                                    data-width="100%" required>
                                    <option value="">Select User</option>
                                    @foreach ($user as $item)
                                        <option value="{{$item->userid}}">{{$item->userid}}({{$item->name}}) {{$item->role == 'franchise' ? '| Franchise' : ''}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="wallet" class="form-label">From Wallet</label>
                                <select class="js-example-basic-single form-control" id="wallet" name="wallet" required>
                                    <option value="">Select Source Wallet</option>
                                    <option value="payin">PayIn</option>
                                    <option value="payout">PayOut</option>
                                    <option value="wallet">Aeps</option>
                                    <option value="hold">Hold wallet</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Transfer Amount</label>
                                <input type="number" class="form-control" id="amount_switcher" placeholder="Enter Amount"
                                    name="amount" min="0.01" step="0.01" required>
                                <div class="form-text">Enter the amount to transfer (always positive)</div>
                            </div>
                            <div class="mb-3">
                                <label for="Twallet" class="form-label">To Wallet</label>
                                <select class="js-example-basic-single form-control" id="Twallet" name="Twallet" required>
                                    <option value="">Select Destination Wallet</option>
                                    <option value="payout">PayOut</option>
                                    <option value="payin">PayIn</option>
                                    <option value="wallet">Aeps</option>
                                    <option value="hold">Hold wallet</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description_switcher" class="form-label">Description (Optional)</label>
                                <input type="text" class="form-control" id="description_switcher" placeholder="Enter description for this transaction"
                                    name="description">
                            </div>
                            <button type="submit" class="btn btn-info me-2">
                                <i class="fas fa-exchange-alt me-1"></i>
                                Transfer Between Wallets
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-money-bill-transfer me-2"></i>
                            Admin Amount Transfer
                        </h6>
                        <p class="text-muted">Add or deduct amount from user's wallet</p>

                        <form class="forms-sample" id="amounttransfer">
                            @csrf
                            <div class="mb-3">
                                <label for="userid" class="form-label">Userid</label>
                                <select class="js-example-basic-single form-select" name="userid" id="userid"
                                    data-width="100%">
                                    <option value="">Select User</option>
                                    @foreach ($user as $item)
                                        <option value="{{$item->userid}}">{{$item->userid}}({{$item->name}}) {{$item->role == 'franchise' ? '| Franchise' : ''}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Transaction Type</label>
                                <select class="form-control" id="transaction_type" name="transaction_type" required>
                                    <option value="">Select Type</option>
                                    <option value="credit">Credit (Add Money)</option>
                                    <option value="debit">Debit (Deduct Money)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" placeholder="Enter Amount"
                                    name="amount" min="0.01" step="0.01" required>
                                <div class="form-text">Enter the amount to transfer (always positive)</div>
                            </div>
                            <div class="mb-3">
                                <label for="wallet" class="form-label">Wallet</label>
                                <select class="form-control" id="wallet" name="wallet" required>
                                    <option value="">Select Wallet</option>
                                    <option value="wallet">Aeps</option>
                                    <option value="hold">Hold wallet</option>
                                    <option value="payin">PayIn</option>
                                    <option value="payout">PayOut</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description (Optional)</label>
                                <input type="text" class="form-control" id="description" placeholder="Enter description for this transaction"
                                    name="description">
                            </div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-exchange-alt me-1"></i>
                                Process Transaction
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        formasync('amounttransferswitcher');
        formasync('amounttransfer');
        
        // Enhanced validation and UI feedback for amount transfer
        $("#amounttransfer").validate({
            rules: {
                userid: { required: true },
                transaction_type: { required: true },
                amount: { required: true, min: 0.01 },
                wallet: { required: true }
            },
            messages: {
                userid: "Please select a user",
                transaction_type: "Please select transaction type",
                amount: "Please enter a valid amount (minimum 0.01)",
                wallet: "Please select a wallet"
            },
            submitHandler: function(form) {
                // Show confirmation dialog for debit transactions
                const transactionType = $('#transaction_type').val();
                const amount = $('#amount').val();
                const userid = $('#userid option:selected').text();
                const wallet = $('#wallet option:selected').text();
                
                let confirmMessage = '';
                if (transactionType === 'debit') {
                    confirmMessage = `Are you sure you want to DEDUCT ₹${amount} from ${userid}'s ${wallet} wallet?`;
                } else {
                    confirmMessage = `Are you sure you want to ADD ₹${amount} to ${userid}'s ${wallet} wallet?`;
                }
                
                if (confirm(confirmMessage)) {
                apex("POST", "{{ url('/admin/api/amounttransfer') }}", new FormData(form), form,
                    "javascript:void(0)", "javascript:void(0)");
                $(form).trigger('reset');
                }
            }
        });
        
        // Dynamic button text and styling based on transaction type
        $('#transaction_type').change(function() {
            const transactionType = $(this).val();
            const submitBtn = $('#amounttransfer button[type="submit"]');
            
            if (transactionType === 'debit') {
                submitBtn.removeClass('btn-primary').addClass('btn-danger');
                submitBtn.html('<i class="fas fa-minus-circle me-1"></i>Deduct Amount');
            } else if (transactionType === 'credit') {
                submitBtn.removeClass('btn-danger').addClass('btn-primary');
                submitBtn.html('<i class="fas fa-plus-circle me-1"></i>Add Amount');
            } else {
                submitBtn.removeClass('btn-danger').addClass('btn-primary');
                submitBtn.html('<i class="fas fa-exchange-alt me-1"></i>Process Transaction');
            }
        });
        
        // Amount switcher validation
        $("#amounttransferswitcher").validate({
            rules: {
                userid: { required: true },
                wallet: { required: true },
                amount: { required: true, min: 0.01 },
                Twallet: { 
                    required: true,
                    notEqualTo: '#wallet'
                }
            },
            messages: {
                userid: "Please select a user",
                wallet: "Please select source wallet",
                amount: "Please enter a valid amount (minimum 0.01)",
                Twallet: {
                    required: "Please select destination wallet",
                    notEqualTo: "Source and destination wallets cannot be the same"
                }
            },
            submitHandler: function(form) {
                // Show confirmation dialog
                const amount = $('#amount_switcher').val();
                const userid = $('#userids option:selected').text();
                const fromWallet = $('#wallet option:selected').text();
                const toWallet = $('#Twallet option:selected').text();
                
                const confirmMessage = `Are you sure you want to transfer ₹${amount} from ${fromWallet} to ${toWallet} for ${userid}?`;
                
                if (confirm(confirmMessage)) {
                apex("POST", "{{ url('/admin/api/amounttransferSelf') }}", new FormData(form), form,
                    "javascript:void(0)", "javascript:void(0)");
                $(form).trigger('reset');
                }
            }
        });
        
        // Add custom validation method for not equal to
        $.validator.addMethod("notEqualTo", function(value, element, param) {
            return this.optional(element) || value !== $(param).val();
        }, "Please select a different value.");
    </script>
@endsection

            return this.optional(element) || value !== $(param).val();
        }, "Please select a different value.");
    </script>
@endsection
