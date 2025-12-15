<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="author" content="Adarsh Pushpendra Pandey">
    <meta name="keywords"
        content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <title>{{ setting('app_name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- End fonts -->

    <!-- core:css -->
    <link rel="stylesheet" href="/admin_assets/vendors/core/core.css">
    <!-- endinject -->

    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="/admin_assets/vendors/sweetalert2/sweetalert2.min.css">
    <!-- End plugin css for this page -->

    <!-- inject:css -->
    <link rel="stylesheet" href="/admin_assets/fonts/feather-font/css/iconfont.css">
    <link rel="stylesheet" href="/admin_assets/vendors/flag-icon-css/css/flag-icon.min.css">
    <!-- endinject -->

    <!-- Layout styles -->
    <link rel="stylesheet" href="/admin_assets/css/demo1/style.css">
    <!-- End layout styles -->

    <link rel="shortcut icon" href="/assets-home/images/logo/logo.jpeg" />
    @yield('css')
</head>

<body>
    @yield('content')

    <!-- core:js -->
    <script src="/admin_assets/vendors/core/core.js"></script>
    <!-- endinject -->

    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->

    <!-- inject:js -->
    <script src="/admin_assets/vendors/feather-icons/feather.min.js"></script>
    <script src="/admin_assets/js/template.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="/admin_assets/js/main.js"></script>
    <script src="/admin_assets/js/sweet-alert.js"></script>
    <script src="/admin_assets/vendors/sweetalert2/sweetalert2.min.js"></script>
    <script src="/admin_assets/js/jquery.validate.min.js"></script>
    <!-- End custom js for this page -->
    <!-- Custom js for this page -->
    @yield('js')
    <!-- End custom js for this page -->

</body>

</html>
