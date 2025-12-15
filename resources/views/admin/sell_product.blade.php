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
                        <h6 class="card-title">{{$title}}</h6>

                        <form class="forms-sample" id="amounttransfer">
                            @csrf
                            <div class="mb-3">
                                <label for="userid" class="form-label">Userid</label>
                                <select class="js-example-basic-single form-select" name="userid" id="userid"
                                    data-width="100%">
                                    <option value="">Select User</option>
                                    @foreach ($user as $item)
                                        <option value="{{$item->userid}}">{{$item->userid}}({{$item->name}})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="product" class="form-label">Product</label>
                                <select class="js-example-basic-single form-select" name="product" id="product"
                                    data-width="100%">
                                    <option value="">Select Product</option>
                                    @foreach ($product as $item)
                                        <option value="{{$item->id}}" amount_doll="{{balance($item->amount,'₹')}}" amount="{{number_format($item->amount,2)}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p id="amount_view"></p>
                            <button type="submit" style="display:none;" class="btn btn-primary me-2" id="sell_now">Sell now</button>
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
                apex("POST", "{{ url('/admin/api/sell_now_product') }}", new FormData(form), form,
                    "/admin/products-sell", "javascript:void(0)");
                $(form).trigger('reset');
            }
        });
        $("#product").on('change',function(){
            let val = $(this).val();
            let amount_doll = $(this).attr('amount_doll');
            let amount = $(this).attr('amount');
            if(val != ""){
                $.ajax({
                type: "get",
                url: "/admin/api/view_product?id="+val,
                dataType: "json",
                success: function (response) {
                    if (response.status == 1) {
                        // message(response);
                        $("#amount_view").html('Your product amount for customer is <button class="btn btn-primary">'+response.body.amount+'</button> <br>Payable amount by Franchise is <button class="btn mt-2 btn-success">'+response.body.pamount+'    </button>');
                        $("#sell_now").show();
                    } else {
                        message(response);
                    }
                },
                error: function (e) {
                    message({status:0,title:'Something wents wrong'});
                }
                });
            }else{
                $("#sell_now").hide();
            }
        });
    </script>
@endsection
