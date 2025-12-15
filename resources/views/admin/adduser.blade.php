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
                        <h6 class="card-title">Add User</h6>

                        <form class="forms-sample" id="amounttransfer">
                            @csrf
                            @foreach ($data as $item)
                            <div class="mb-3">
                                <label for="amount" class="form-label">{{$item['title']}}</label>
                                <input type="text" class="form-control" id="{{$item['name']}}" placeholder="Enter {{$item['title']}}"
                                    name="{{$item['name']}}">
                            </div>
                            @endforeach
                            <button type="submit" class="btn btn-primary me-2">Create User</button>
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
                apex("POST", "{{ url('/admin/api/add_user') }}", new FormData(form), form,
                    "/admin/userlist", "javascript:void(0)");
                $(form).trigger('reset');
            }
        });
    </script>
@endsection
