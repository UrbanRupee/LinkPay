<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ setting('app_name') }} - Forgot Password</title>
    <meta charset="utf-8" />
    <meta name="robots" content="noindex, follow" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="TimeUp is a digital marketing company">
    <meta name="title" Content="TiMEUP MARKETING PVT LTD - Home">
    <meta name="description"
        content="TiMEUP MARKETING PVT LTD is a successful multilevel marketing company. Lorem Ipsum is simply dummied text of the printing and typesetting industry. Lorem Ipsum has been the industry&#039;s standard dummy text ever since the 1500s when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularly raised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.

Why do we use it?
It is a long-established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#039;Content here, content here, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for &#039;lorem Ipsum will uncover many websites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humor and the like).">

    <meta name="keywords" content="TiMEUP MARKETING PVT LTD,timeup,blog,manage,mlm,mlmlab,binary mlm,php mlm">
    <meta name="google-site-verification" content="5RX12CwxHKIfmmFa_hvPBYJc9hQr8QFQsLlREz-_uMg">
    <meta name="robots" content="noindex,nofollow">
    <!-- Favicon -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="TiMEUP MARKETING PVT LTD - Home">
    <meta name="title" Content="TiMEUP MARKETING PVT LTD - Home">
    <meta name="description"
        content="TiMEUP MARKETING PVT LTD is a successful multilevel marketing company. Lorem Ipsum is simply dummied text of the printing and typesetting industry. Lorem Ipsum has been the industry&#039;s standard dummy text ever since the 1500s when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularly raised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.

Why do we use it?
It is a long-established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using &#039;Content here, content here, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for &#039;lorem Ipsum will uncover many websites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humor and the like).">

    <meta name="keywords" content="timeup,blog,manage,mlm,mlmlab,binary mlm,php mlm">
    <meta name="google-site-verification" content="5RX12CwxHKIfmmFa_hvPBYJc9hQr8QFQsLlREz-_uMg">
    <meta name="robots" content="noindex,nofollow">
    <!-- Favicon -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="TiMEUP MARKETING PVT LTD - Home">
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/logo/logo.png">
    <link rel="stylesheet" href="/assets/css/login.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/admin_assets/vendors/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
        integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        label.error {
            color: red;
        }
    </style>
</head>

<body>
    <div class="preloader">
        <div class="loader">
            <div class="loader-box-1"></div>
            <div class="loader-box-2"></div>
        </div>
    </div>
    <div class="login-container">
        <div class="row grid">
            <div class="col-lg-6 left">
                <div class="login-header">
                    <div class="logo"></div>
                    <a href="{{ url('/') }}" class="home">Home</a>
                </div>
                <form class="login-form" method="post" id="register_form" style="justify-content: flex-start;">
                    @csrf
                    <div class="first-header">If you have forgotten your password, enter your username and we will email
                        you your password and transactional password..</div>
                    <div class="second-header">Forgot Password</div>
                    <div class="row" style="width: 100%;">
                        <div class="col-lg-12">
                            <div class="inputContainer margin-t-30">
                                <input type="text" name="username" id="username" class="input form-control"
                                    placeholder="UserId/Mobile No" required />
                                <div class="user-name">
                                    <i class="fa fa-user"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="login" style="margin-bottom: 10px">
                            Send Password On Email
                        </button>
                    </div>
                    <div class="" style="text-align: center;width: 100%;margin-bottom: 20px;font-size: 1rem;">
                        Already have an account?
                        <a href="{{ url('/login') }}" class="loginIn">Login</a>
                    </div>
                </form>
            </div>
            <div class="col-lg-6 right">
                <div class="hello-logo" style="display: flex; justify-content: center">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" width="60%" />
                </div>
                <div class="hello" style="display: flex; justify-content: center">
                    <img src="https://nisp.mic.gov.in/assets/iaProfile/img/login-image.png" class="sign-up-img"
                        width="100%" />
                </div>
            </div>
        </div>
        <!-- Main JS -->
        <script src="/admin_assets/js/main.js"></script>
        <script src="/admin_assets/js/sweet-alert.js"></script>
        <script src="/admin_assets/vendors/sweetalert2/sweetalert2.min.js"></script>
        <script src="/admin_assets/js/jquery.validate.min.js"></script>
        <script>
            formasync('register_form');
            $("#register_form").validate({
                submitHandler: function(form) {
                    apex("POST", "{{ url('/auth/forget-password') }}", new FormData(form), form,
                        "/login", "#");
                }
            });
        </script>
</body>

</html>
