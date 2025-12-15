@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">Transaction Password</h2>
        </div>
        <form action="#" id="edit_profile" class="form-horizontal">
            @csrf
            <div class="row">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label class="form-label" for="default-input">User ID</label>
                                            <input class="form-control" type="text" id="default-input"
                                                value="{{ user('userid') }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-4">
                                            <label class="form-label" for="otp">Enter OTP</label>
                                            <input class="form-control" type="text" id="otp" name="otp">
                                            <button type="button" onclick="sendotpfortranspassword('{{ user('userid') }}',this)"
                                                class="btn btn-primary btn-sm">Send OTP</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="form-label" for="newpassword">New Password</label>
                                            <input class="form-control" type="password" id="newpassword" name="newpassword"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="form-label" for="repassword">Re-Enter New Password</label>
                                            <input class="form-control" type="password" id="repassword" name="repassword"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="md-4">
                                            <input type="checkbox" id="show_password" name="show_password">
                                            <label class="form-label" for="show_password">Show Password</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-primary w-md">Update Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection

@section('js')
    <script>
        $("#show_password").on('change',function(){
            let pass = $("#newpassword");
            let repass = $("#repassword");
            if (pass.attr('type') == 'text' && repass.attr('type') == 'text') {
                pass.attr('type','password');
                repass.attr('type','password');
            }else if (pass.attr('type') == 'password' && repass.attr('type') == 'password') {
                pass.attr('type','text');
                repass.attr('type','text');
            }else{
                pass.attr('type','password');
                repass.attr('type','password');
            }
        });
        formasync('edit_profile');
        $("#edit_profile").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/api/transaction_password') }}", new FormData(form), form, "javascript:;", "javascript:;");
            }
        });

        function sendotpfortranspassword(userid,a) {
            let data = new FormData();
            data.append('userid', userid);
            data.append('_token', '{{csrf_token()}}');
            $(a).html('Sending');
            apex("POST", "{{ url('/api/send-otp') }}", data, '', "javascript:;", "javascript:;");
            $(a).html('Sended');
            setTimeout(() => {
                $(a).html('Send OTP');
            }, 10000);
        }
    </script>
@endsection
