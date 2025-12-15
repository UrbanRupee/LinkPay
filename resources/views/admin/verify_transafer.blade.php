@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Transaction</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Verify Transaction</h6>

                        <form class="forms-sample" id="amounttransfer">
                            @csrf
                            <div class="mb-3">
                                <label for="amount" class="form-label">Transaction Id</label>
                                <input type="text" class="form-control" id="trnid" placeholder="Enter Transaction Id" name="trnid">
                            </div>
                            <div class="mb-3">
                                <label for="userid" class="form-label">Status</label>
                                <select class="js-example-basic-single form-select" name="trnstatus" id="trnstatus" data-width="100%">
                                    <option value="">Select Status</option>
                                    <option value="1">Success</option>
                                    <option value="0">Failed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">UTR No.</label>
                                <input type="text" class="form-control" id="utr" placeholder="Enter UTR No." name="utr">
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Check</button>
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
                apex("POST", "{{ url('/admin/api/transactionVerify') }}", new FormData(form), form,
                    "javascript:void(0)", "javascript:void(0)");
                $(form).trigger('reset');
            }
        });
    </script>
@endsection
