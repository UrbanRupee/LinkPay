@extends('user.layout.NewUser')

@section('css')
    <style>
        /* Re-using general styles for consistency from other pages */
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary-text-color);
        }

        /* Form specific styles */
        .form-group label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: var(--border-radius-md);
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            /*background-color: var(--primary-color);*/
            border-color: var(--primary-color);
            border-radius: var(--border-radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .text-danger {
            color: #dc3545 !important;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        /* Ensure consistent padding for form rows/columns */
        .form-row > .col, .form-row > [class*="col-"] {
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center"> {{-- Centering the card on the page --}}
        <div class="col-xl-8 col-lg-10"> {{-- Adjust column size for responsiveness --}}
            <div class="box"> {{-- Using the .box style from the new layout --}}
                <div class="card-header">
                    <h5>Change Password</h5>
                </div>
                <div class="card-body"> {{-- Using .card-body for Bootstrap 4 consistency --}}
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
                            <input class="form-control" type="password" id="password" name="password" required placeholder="Enter new password">
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label" for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                            <input class="form-control" type="password" id="password_confirmation" name="repassword" required placeholder="Re-enter new password">
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">Update Password</button>
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
            // jQuery Validate for the Reset Password Form
            $("#resetPasswordForm").validate({
                rules: {
                    current_password: {
                        required: true,
                        minlength: 6 // Adjust minimum length as per your password policy
                    },
                    password: {
                        required: true,
                        minlength: 6 // Adjust minimum length as per your password policy
                    },
                    password_confirmation: {
                        required: true,
                        minlength: 6,
                        equalTo: "#password" // Ensures confirmation matches new password
                    }
                },
                messages: {
                    current_password: {
                        required: "Please enter your current password."
                    },
                    password: {
                        required: "Please enter a new password.",
                        minlength: "Your new password must be at least {0} characters long."
                    },
                    password_confirmation: {
                        required: "Please confirm your new password.",
                        minlength: "Your confirmed password must be at least {0} characters long.",
                        equalTo: "New password and confirmation do not match."
                    }
                },
                errorClass: "text-danger", // Apply danger text color for errors
                validClass: "is-valid", // Add a class for valid fields (optional)
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass(errorClass).removeClass(validClass);
                    $(element).closest('.form-group').find('label').addClass('text-danger'); // Highlight label
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass(errorClass).addClass(validClass);
                    $(element).closest('.form-group').find('label').removeClass('text-danger'); // Remove highlight
                },
                submitHandler: function(form) {
                    // Show a confirmation dialog before submitting
                    Swal.fire({
                        title: 'Change Password?',
                        text: 'Are you sure you want to change your password?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // If confirmed, proceed with AJAX submission
                            // Using the `apex` function you previously had
                            apex("POST", $(form).attr('action'), new FormData(form), form,
                                "{{ url('logout') }}", // Redirect to logout after password change for security
                                "#"); // Error redirect or leave blank for default
                        }
                    });
                }
            });
        });
    </script>
@endsection