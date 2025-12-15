@extends('user.layout.auth')
@section('content')
    <div class="main-wrapper">
        <div class="page-wrapper full-page">
            <div class="page-content d-flex align-items-center justify-content-center">

                <div class="row w-100 mx-0 auth-page">
                    <div class="col-md-8 col-xl-6 mx-auto">
                        <div class="card">
                            <div class="row">
                                <div class="col-md-4 pe-md-0">
                                    <div class="auth-side-wrapper">

                                    </div>
                                </div>
                                <div class="col-md-8 ps-md-0">
                                    <div class="auth-form-wrapper px-4 py-5">
                                        <a href="#" class="noble-ui-logo d-block mb-2">Time<span>Up</span></a>
                                        <h5 class="text-muted fw-normal mb-4">Welcome back! Log in to your account.</h5>
                                        <form class="forms-sample" id="loginform">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Email or Mobile No.</label>
                                                <input type="text" class="form-control" id="username" name="username"
                                                    placeholder="Email or Mobile No" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="userPassword" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="userPassword"
                                                    autocomplete="current-password" placeholder="Password" name="password" required>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="authCheck" name="remember">
                                                <label class="form-check-label" for="authCheck">
                                                    Remember me
                                                </label>
                                            </div>
                                            <div>
                                                <button class="btn btn-primary text-white me-2 mb-2 mb-md-0" type="submit">Sign in</button>
                                                {{-- <button type="button" onclick="showSwal('mixin')"
                                                    class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">
                                                    <i class="btn-icon-prepend" data-feather="twitter"></i>
                                                    Login with twitter
                                                </button> --}}
                                            </div>
                                            <a href="/register" class="d-block mt-3 text-muted">Not a user? Sign
                                                up</a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        formasync('loginform');
        $("#loginform").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/login') }}", new FormData(form), form, "/dashboard", "#");
            }
        });
    </script>
@endsection