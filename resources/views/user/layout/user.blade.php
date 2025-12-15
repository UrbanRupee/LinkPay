<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="author" content="Adarsh Pushpendra Pandey">
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
    <link rel="stylesheet" href="/admin_assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css">
    <!-- End plugin css for this page -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="/admin_assets/vendors/flatpickr/flatpickr.min.css">

    <link rel="stylesheet" href="/admin_assets/vendors/sweetalert2/sweetalert2.min.css">
    <!-- End plugin css for this page -->
    <link rel="stylesheet" href="/admin_assets/vendors/pickr/themes/classic.min.css">

    <!-- inject:css -->
    <link rel="stylesheet" href="/admin_assets/fonts/feather-font/css/iconfont.css">
    <link rel="stylesheet" href="/admin_assets/vendors/flag-icon-css/css/flag-icon.min.css">
    
    <link rel="stylesheet" href="/admin_assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/jquery-tags-input/jquery.tagsinput.min.css">
	<link rel="stylesheet" href="/admin_assets/vendors/dropzone/dropzone.min.css">
	<link rel="stylesheet" href="/admin_assets/vendors/dropify/dist/dropify.min.css">
	<link rel="stylesheet" href="/admin_assets/vendors/pickr/themes/classic.min.css">
	<link rel="stylesheet" href="/admin_assets/vendors/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="/admin_assets/vendors/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/admin_assets/css/demo1/style.css">
    <link rel="shortcut icon" href="/assets/images/logo/logo.png" />
    
    @yield('css')
</head>

<body>
    <div class="main-wrapper">

        <!-- partial:partials/_sidebar.html -->
        @include('user.include.sidebar')
        <!-- partial -->

        <div class="page-wrapper">

            <!-- partial:partials/_navbar.html -->
            @include('user.include.navbar')
            <!-- partial -->

            @yield('content')

            <!-- partial:partials/_footer.html -->
            @include('user.include.footer')
            <!-- partial -->

        </div>
    </div>

    <!-- core:js -->
    <script src="/admin_assets/vendors/core/core.js"></script>
    <!-- endinject -->

    <!-- Plugin js for this page -->
    <script src="/admin_assets/vendors/flatpickr/flatpickr.min.js"></script>
    <script src="/admin_assets/vendors/apexcharts/apexcharts.min.js"></script>
    <script src="/admin_assets/vendors/pickr/pickr.min.js"></script>
    <!-- End plugin js for this page -->

    <script src="/admin_assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="/admin_assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js"></script>

    <script src="/admin_assets/vendors/select2/select2.min.js"></script>
	<script src="/admin_assets/vendors/inputmask/jquery.inputmask.min.js"></script>
	<script src="/admin_assets/vendors/typeahead.js/typeahead.bundle.min.js"></script>
	<script src="/admin_assets/vendors/jquery-tags-input/jquery.tagsinput.min.js"></script>
	<script src="/admin_assets/vendors/dropzone/dropzone.min.js"></script>
	<script src="/admin_assets/vendors/dropify/dist/dropify.min.js"></script>
	<script src="/admin_assets/vendors/pickr/pickr.min.js"></script>
	<script src="/admin_assets/vendors/moment/moment.min.js"></script>
	<script src="/admin_assets/vendors/flatpickr/flatpickr.min.js"></script>

    <!-- inject:js -->
    <script src="/admin_assets/vendors/feather-icons/feather.min.js"></script>
    <script src="/admin_assets/js/template.js"></script>
    <!-- endinject -->

    <!-- Custom js for this page -->
    <script src="/admin_assets/js/main.js"></script>
    <script src="/admin_assets/js/dashboard-light.js"></script>
    <script src="/admin_assets/js/sweet-alert.js"></script>
    <script src="/admin_assets/vendors/sweetalert2/sweetalert2.min.js"></script>
    <script src="/admin_assets/js/jquery.validate.min.js"></script>
    <script src="/admin_assets/js/data-table.js"></script>

    <script src="/admin_assets/js/inputmask.js"></script>
	<script src="/admin_assets/js/select2.js"></script>
	<script src="/admin_assets/js/typeahead.js"></script>
	<script src="/admin_assets/js/tags-input.js"></script>
	<script src="/admin_assets/js/dropzone.js"></script>
	<script src="/admin_assets/js/dropify.js"></script>
	<script src="/admin_assets/js/pickr.js"></script>
	<script src="/admin_assets/js/flatpickr.js"></script>

    <!-- End custom js for this page -->
    @yield('js')
</body>

</html>
