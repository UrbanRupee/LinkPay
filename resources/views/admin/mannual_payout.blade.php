@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Settlement</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Mannual Payout</h6>

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
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" placeholder="Enter Amount"
                                    name="amount">
                            </div>
                            <div class="mb-3">
                                <label for="holder_name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="holder_name" placeholder="Enter Name"
                                    name="holder_name">
                            </div>
                            <div class="mb-3">
                                <label for="account_no" class="form-label">Account No.</label>
                                <input type="text" class="form-control" id="account_no" placeholder="Enter Account No"
                                    name="account_no">
                            </div>
                            <div class="mb-3">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" id="ifsc_code" placeholder="Enter IFSC Code"
                                    name="ifsc_code">
                            </div>
                            <div class="mb-3">
                                <label for="utr" class="form-label">UTR No.</label>
                                <input type="text" class="form-control" id="utr" placeholder="Enter UTR No."
                                    name="utr">
                            </div>
                            <div class="mb-3">
                                <label for="created_at" class="form-label">Created At</label>
                                <input type="text" class="form-control" id="created_at" placeholder="Enter Created At" value="{{date('Y-m-d H:i:s')}}"
                                    name="created_at">
                            </div>
                            <div class="mb-3">
                                <label for="mode" class="form-label">Mode of Transaction</label>
                                <select class="js-example-basic-single form-select" name="mode" id="mode"
                                    data-width="100%">
                                    <option value="">Select Mode</option>
                                    <option value="Mannual">Mannual</option>
                                    <option value="Settlement">Settlement</option>
                                    <option value="IMPS">IMPS</option>
                                    <option value="NEFT">NEFT</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Initiate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        formasync('amounttransfer');
        $("#amounttransfer").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/admin/api/payout_mannual') }}", new FormData(form), form,
                    "javascript:void(0)", "javascript:void(0)");
            }
        });
    </script>
@endsection
