@extends('user.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">User Activation</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Activation</h6>
                        <form class="forms-sample" id="edit_profile">
                            @csrf
                            <div class="row mb-12">
                                <div class="col">
                                    <label class="form-label">Sponser Id:</label>
                                    <input class="form-control mb-4 mb-md-0" value="{{ user('userid') }}" name="userid"
                                        readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">Available Amount:</label>
                                    <input type="text" class="form-control mb-4 mb-md-0" value="{{wallet(user('userid'),null,'amount')}}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col">
                                        <label class="form-label">User Id:</label>
                                        <input type="text" class="form-control mb-4 mb-md-0" name="userid"
                                            id="userid" required>
                                        <p id="username_container"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col">
                                        <label class="form-label">Package:</label>
                                        <select class="form-control mb-4 mb-md-0" name="package" id="package">
                                            <option value="">Select Package</option>
                                            @foreach ($package as $item)
                                                <option value="{{ $item->id }}">{{ strtoupper($item->name) }}({{$item->amount}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <button type="submit" class="btn btn-primary">Activate Now</button>
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
        formasync('edit_profile');
        $("#userid").on('change', function() {
            let idcon = this;
            $.ajax({
                type: "post",
                url: "{{url('/api/usercheck')}}",
                data: {
                    'userid': $(this).val()
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 1) {
                        $("#username_container").html(response.data);
                    }else{
                        $(idcon).val('');
                        $("#username_container").html(response.data);
                    }
                },
                error: function(e) {}
            });
        });
        $("#edit_profile").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/api/upgrade_id') }}", new FormData(form), form,
                    "{{ url('dashboard') }}",
                    "#");
            }
        });
    </script>
@endsection
