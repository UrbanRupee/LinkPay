@extends('user.layout.auth') {{-- EXTENDS THE NEW AUTH LAYOUT --}}

@section('content')
    <form class="forms-sample" id="loginform">
        @csrf
        <div class="mb-3">
            {{-- Label is technically hidden by CSS but good for accessibility --}}
            <label for="username" class="form-label">Merchant ID</label>
            <input type="text" class="form-control" id="username" name="username"
                placeholder="Enter your email or Merchant ID" required autocomplete="username">
        </div>
        <div class="mb-3">
            {{-- Label is technically hidden by CSS but good for accessibility --}}
            <label for="userPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="userPassword" name="password"
                autocomplete="current-password" placeholder="Enter your password" required>
        </div>
        {{-- Razorpay's design doesn't show "Remember Me" checkbox on first screen --}}
        {{-- You can uncomment this if you absolutely need it, but it deviates from the inspiration --}}
        {{-- <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="rememberMeCheck" name="remember">
            <label class="form-check-label" for="rememberMeCheck">
                Remember me
            </label>
        </div> --}}
        <div>
            <button class="btn btn-primary" type="submit">Continue</button> {{-- Changed text to "Continue" --}}
        </div>

        <p class="form-footer-legal-text">
            By continuing you agree to our <a href="#">privacy policy</a> and <a href="#">terms of use</a>
            {{-- Replace # with actual links to your policy and terms --}}
        </p>

    </form>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // jQuery Validate for the login form
            $("#loginform").validate({
                rules: {
                    username: {
                        required: true,
                        minlength: 3 // Adjust min length based on your Retailer ID format
                    },
                    password: {
                        required: true,
                        minlength: 6 // Adjust min length based on your password policy
                    }
                },
                messages: {
                    username: {
                        required: "Please enter your email or Merchant ID.",
                        minlength: "Your input must be at least {0} characters long."
                    },
                    password: {
                        required: "Please enter your password.",
                        minlength: "Your password must be at least {0} characters long."
                    }
                },
                errorClass: "text-danger", // Class for error messages
                // No validClass and unhighlight as per Razorpay's minimalist error display
                highlight: function(element, errorClass) {
                    $(element).addClass(errorClass);
                },
                unhighlight: function(element, errorClass) {
                    $(element).removeClass(errorClass);
                },
                submitHandler: function(form) {
                    // Disable submit button
                    var submitBtn = $(form).find('button[type=submit]');
                    submitBtn.prop('disabled', true).html('Logging in...');

                    // Make AJAX request
                    $.ajax({
                        type: 'POST',
                        url: "{{ url('/login') }}",
                        data: new FormData(form),
                        contentType: false,
                        cache: false,
                        processData: false,
                        dataType: "json",
                        success: function(response) {
                            if (response.status == 1) {
                                // Show success message
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Redirect to appropriate dashboard
                                    window.location.href = response.redirect;
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error'
                                });
                                submitBtn.prop('disabled', false).html('Continue');
                            }
                        },
                        error: function(xhr) {
                            var errorMessage = 'Login failed. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error'
                            });
                            submitBtn.prop('disabled', false).html('Continue');
                        }
                    });
                }
            });
        });
    </script>
@endsection