@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Change Password</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{$title}}</h6>

                        <form class="forms-sample" id="reset_password">
                            @csrf
                            <input type="hidden" name="userid" value="{{session()->get('adminlogin')['userid']}}">
                            <div class="mb-3">
                                <label for="password" class="form-label">Enter New Password</label>
                                <input type="password" class="form-control" id="password" autocomplete="off"
                                    placeholder="Enter New Password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="repassword" class="form-label">Re enter new password</label>
                                <input type="password" class="form-control" id="repassword" autocomplete="off"
                                    placeholder="Re-enter New Password" name="repassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    formasync('reset_password');
    $("#reset_password").validate({
        submitHandler: function(form){
            apex("POST", "{{ url('/admin/api/reset_password') }}", new FormData(form), form, "/admin/logout", "#");
        }
    });
</script>
@endsection
