@extends('user.layout.NewUser')

@section('css')
    <style>
        /* RED & BLACK THEME - Reset Password Page */
        .password-page-content {
            background: var(--black);
            padding: 1.5rem;
            min-height: 100vh;
        }

        .password-card {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            border-left: 4px solid var(--primary-orange);
        }

        .password-card .card-header {
            background: var(--black);
            border-bottom: 2px solid var(--primary-orange);
            padding: 1.25rem 1.5rem;
        }

        .password-card .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: #FFFFFF;
            font-size: 1.25rem;
        }

        .password-card .card-body {
            padding: 2rem 1.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #FFFFFF;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background-color: var(--medium-gray) !important;
            border: 1px solid var(--primary-orange) !important;
            color: #FFFFFF !important;
            border-radius: var(--border-radius-md);
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            background-color: var(--dark-gray) !important;
            border-color: var(--primary-orange) !important;
            box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25) !important;
        }

        .form-control:disabled {
            background-color: var(--black) !important;
            color: #9CA3AF !important;
            opacity: 0.7;
        }

        .btn-primary {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius-md);
        }

        .btn-primary:hover {
            background-color: var(--dark-red) !important;
            border-color: var(--dark-red) !important;
        }

        .text-danger {
            color: var(--light-orange) !important;
        }

        .is-invalid {
            border-color: var(--light-orange) !important;
        }
    </style>
@endsection

@section('content')
<div class="password-page-content">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10">
            <div class="password-card">
                <div class="card-header">
                    <h5>🔒 Reset Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ url('/api/reset_password') }}" method="POST" id="resetPasswordForm">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="form-label" for="user_id_display">User ID</label>
                            <input class="form-control" type="text" id="user_id_display" value="{{ user('userid') }}" disabled>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label" for="current_password">Current Password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" id="current_password" name="current_password" required placeholder="Enter your current password">
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" id="password" name="password" required placeholder="Enter new password (min 6 characters)">
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label" for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" id="password_confirmation" name="repassword" required placeholder="Re-enter new password">
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="lock" style="width:16px; height:16px; margin-right:0.5rem;"></i>
                                Update Password
                            </button>
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
        $(document).ready(function() {
            // Initialize Lucide icons
            lucide.createIcons();

            // jQuery Validate for the Reset Password Form
            $("#resetPasswordForm").validate({
                rules: {
                    current_password: {
                        required: true,
                        minlength: 6
                    },
                    password: {
                        required: true,
                        minlength: 6
                    },
                    repassword: {
                        required: true,
                        minlength: 6,
                        equalTo: "#password"
                    }
                },
                messages: {
                    current_password: {
                        required: "Please enter your current password.",
                        minlength: "Password must be at least 6 characters long."
                    },
                    password: {
                        required: "Please enter a new password.",
                        minlength: "Your new password must be at least 6 characters long."
                    },
                    repassword: {
                        required: "Please confirm your new password.",
                        minlength: "Your confirmed password must be at least 6 characters long.",
                        equalTo: "New password and confirmation do not match."
                    }
                },
                errorClass: "text-danger",
                validClass: "is-valid",
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid').removeClass(validClass);
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid').addClass(validClass);
                },
                submitHandler: function(form) {
                    Swal.fire({
                        title: 'Change Password?',
                        text: 'Are you sure you want to change your password?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#F15A22',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, change it!',
                        background: '#1F1F1F',
                        color: '#FFFFFF'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            apex("POST", $(form).attr('action'), new FormData(form), form,
                                "{{ url('logout') }}", "#");
                        }
                    });
                }
            });
        });
    </script>
@endsection







