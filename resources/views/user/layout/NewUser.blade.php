<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description"
        content="Welcome to {{ setting('app_name') }}, Introducing a new technology of making money. It is US based program, which is latest, fully automated and highly secure with latest technology. Our highly educated and skilled Developers are managing and upgrading this Program." />
    <meta name="keywords" content="" />
    <title>{{ setting('app_name') }} Member Panel</title>
    <meta data-react-helmet="true" property="og:image" content="{{ url('assets-home/images/logo/logo.jpeg') }}" />
    <meta property="og:site_name" content="{{ setting('app_name') }}" />
    <meta property="og:title" content="{{ setting('app_name') }} " />
    <meta property="og:description"
        content="Welcome to {{ setting('app_name') }}, Introducing a new technology of making money. It is US based program, which is latest, fully automated and highly secure with latest technology. Our highly educated and skilled Developers are managing and upgrading this Program." />
    <link rel="icon" type="image/jpeg" href="{{ url('assets-home/images/logo/logo.jpeg') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/assets-home/images/logo/logo.jpeg">
    <meta name="theme-color" content="#ffffff">

    {{-- Core Styles --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"
        integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous" />
    <link rel="stylesheet" href="/admin_assets/vendors/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.dataTables.css" />

    {{-- Custom App Styles --}}
    {{-- This will contain the specific styles to match your screenshot --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'); /* Modern Font */

        :root {
            /* ORANGE, CREAM & WHITE THEME - MATCHING ADMIN PANEL */
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
            --sidebar-bg: #FFFFFF; /* White Sidebar */
            --sidebar-active-bg: #F15A22; /* Orange Active */
            --sidebar-text-color: #1F2937; /* Dark Text */
            --sidebar-active-text-color: #FFFFFF; /* White on Active */
            --topbar-bg: #FFFFFF; /* White Topbar */
            --main-bg: #FFEDD5; /* Cream Background */
            --card-bg: #FFFFFF; /* White Cards */
            --primary-text-color: #1F2937; /* Dark Text */

            /* ORANGE & CREAM Card Colors - NO GRADIENT */
            --card-color-1: #F15A22; /* Primary Orange */
            --card-color-2: #D14A15; /* Dark Orange */
            --card-color-3: #FFEDD5; /* Light Cream */
            --card-color-4: #F15A22; /* Primary Orange */
            --card-color-5: #FFEDD5; /* Light Cream */
            --card-color-6: #D14A15; /* Dark Orange */

            --primary-orange: #F15A22;
            --dark-orange: #D14A15;
            --light-orange: #FFEDD5;
            --white: #FFFFFF;
            --cream: #FFEDD5;
            --text-dark: #1F2937;
            --text-light: #6B7280;

            --border-radius-md: 0.5rem;
            --shadow-sm: 0 2px 8px rgba(241, 90, 34, 0.15);
            --shadow-md: 0 4px 12px rgba(241, 90, 34, 0.25);
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--main-bg); /* Pure Black */
            color: var(--primary-text-color); /* Light Gray Text */
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling - WHITE WITH ORANGE BORDER */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text-color);
            padding-top: 1rem;
            box-shadow: 4px 0 20px rgba(241, 90, 34, 0.1);
            border-right: 3px solid var(--primary-orange);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            transition: width 0.3s ease, left 0.3s ease;
            z-index: 1050;
            -webkit-overflow-scrolling: touch;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1rem 1.5rem;
            text-align: center;
            border-bottom: 3px solid var(--primary-orange);
            margin-bottom: 1rem;
            position: relative;
            background-color: var(--white);
        }

        .sidebar-logo {
            max-width: 100%;
            height: auto;
        }
        .sidebar.collapsed .sidebar-logo {
             max-width: 60px;
        }


        .sidebar-toggler {
            background: none;
            border: none;
            color: var(--primary-orange);
            font-size: 1.2rem;
            cursor: pointer;
            position: absolute;
            right: 1rem;
            top: 1rem;
            transition: color 0.3s ease;
        }
        .sidebar-toggler:hover { color: var(--dark-orange); }

        /* Mobile specific toggler */
        .mobile-sidebar-close-btn {
            display: none;
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: var(--primary-orange);
            font-size: 1.5rem;
            background: none;
            border: none;
            z-index: 1051;
        }


        .sidebar-nav .nav-item .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--sidebar-text-color);
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-radius: var(--border-radius-md);
            margin: 0.25rem 0.75rem;
        }

        .sidebar-nav .nav-item .nav-link:hover {
            background-color: rgba(241, 90, 34, 0.1);
            color: var(--primary-orange);
        }
        
        .sidebar-nav .nav-item .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text-color);
            font-weight: 600;
        }

        .sidebar-nav .nav-item .nav-link .menu-icon {
            margin-right: 0.75rem;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            color: var(--primary-orange);
        }
        
        .sidebar-nav .nav-item .nav-link.active .menu-icon {
            color: var(--white);
        }
        
        /* Hide titles and arrows when collapsed */
        .sidebar.collapsed .sidebar-nav .nav-item .nav-link .menu-title,
        .sidebar.collapsed .sidebar-nav .nav-item .nav-link .dropdown-arrow {
            display: none;
        }

        /* Adjust icon margin when collapsed */
        .sidebar.collapsed .sidebar-nav .nav-item .nav-link {
            justify-content: center;
            padding: 0.75rem;
        }
        
        .sidebar.collapsed .sidebar-nav .nav-item .nav-link .menu-icon {
            margin-right: 0;
        }

        .sidebar-nav .sub-menu {
            list-style: none;
            padding-left: 0;
            background-color: rgba(241, 90, 34, 0.05);
            border-radius: var(--border-radius-md);
            margin: 0.25rem 0.75rem;
        }

        .sidebar-nav .sub-menu .nav-item .nav-link {
            padding: 0.5rem 1.5rem 0.5rem 2.5rem;
            font-size: 0.9rem;
        }
        
        .sidebar-nav .nav-item .nav-link .dropdown-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .sidebar-nav .nav-item .nav-link[aria-expanded="true"] .dropdown-arrow {
            transform: rotate(90deg);
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            margin-left: var(--sidebar-width); /* Space for fixed sidebar */
            transition: margin-left 0.3s ease;
            background-color: var(--main-bg); /* Lighter yellow background */
            min-height: 100vh; /* Ensure it covers full height */
            padding-top: 5rem; /* Space for sticky top bar */
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width); /* Adjust margin when sidebar is collapsed */
        }

        /* Top Bar Styling - WHITE WITH ORANGE BORDER */
        .top-bar {
            background-color: var(--topbar-bg);
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            border-bottom: 3px solid var(--primary-orange);
            position: fixed;
            color: var(--text-dark);
            top: 0;
            left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            z-index: 1040;
            transition: left 0.3s ease, width 0.3s ease;
            height: 4.5rem;
        }

        .top-bar.expanded {
            left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        .top-bar .breadcrumb {
            margin-bottom: 0;
            background: none;
            padding: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-orange);
        }

        .user-menu-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: var(--text-dark);
            text-decoration: none;
        }

        .user-menu-dropdown .dropdown-toggle img {
            border: 2px solid var(--primary-orange);
            object-fit: cover;
        }

        .user-menu-dropdown .dropdown-toggle .user-name {
            font-weight: 600;
            margin-left: 0.75rem;
            color: var(--text-dark);
        }

        .user-menu-dropdown .dropdown-toggle .user-role {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .user-menu-dropdown .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-md);
            border-radius: var(--border-radius-md);
            padding: 0.5rem 0;
        }

        .user-menu-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            color: var(--primary-text-color);
            transition: background-color 0.2s ease;
        }

        .user-menu-dropdown .dropdown-item:hover {
            background-color: rgba(0, 0, 0, 0.05); /* Light hover effect */
            color: var(--primary-color); /* Standard primary color for hover */
        }

        .user-menu-dropdown .dropdown-item i {
            margin-right: 0.75rem;
            width: 18px;
            height: 18px;
        }

        /* Content Area General Styles */
        .content-body { /* Renamed from .content to avoid Bootstrap conflict */
            padding: 1.5rem; /* Standard padding for content */
        }

        .box {
            background-color: var(--card-bg);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05); /* Subtle border for cards */
        }

        .alert-primary-soft {
            background-color: rgba(0, 123, 255, 0.1);
            color: #004085;
            border-color: rgba(0, 123, 255, 0.2);
        }

        /* Specific styles for dashboard items (matching screenshot colors) */
        .dashboard-card-link {
            text-decoration: none;
            display: block;
            height: 100%;
        }
        .dashboard-card {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            border-radius: var(--border-radius-md);
            color: #fff; /* Text color for gradient cards */
            box-shadow: var(--shadow-md);
            transition: transform 0.2s ease-in-out;
            min-height: 100px; /* Ensure a minimum height for cards */
        }
        .dashboard-card:hover {
            transform: translateY(-5px); /* Slight lift on hover */
        }

        .dashboard-card .icon-box {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            padding: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.3);
            margin-right: 1rem;
        }
        .dashboard-card .icon-box i {
            color: #fff;
        }
        .dashboard-card .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        .dashboard-card .card-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
        }


        /* Gradient Backgrounds from Screenshot */
        .bg-color-1 { background-color: var(--card-color-1); }
        .bg-color-2 { background-color: var(--card-color-2); }
        .bg-color-3 { background-color: var(--card-color-3); }
        .bg-color-4 { background-color: var(--card-color-4); }
        .bg-color-5 { background-color: var(--card-color-5); }
        .bg-color-6 { background-color: var(--card-color-6); }

        .badge {
            padding: 0.45em 0.7em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
        }
        .badge.bg-success { background-color: #28a745; }
        .badge.bg-warning { background-color: #ffc107; color: #212529; }
        .badge.bg-danger { background-color: #dc3545; }
        .badge.bg-info { background-color: #17a2b8; }


        /* Responsive Adjustments */
        @media (max-width: 991.98px) { /* Bootstrap's lg breakpoint */
            .sidebar {
                left: calc(-1 * var(--sidebar-width)); /* Hide sidebar off-screen */
            }
            .sidebar.active {
                left: 0; /* Show sidebar when active */
            }
            .main-content {
                margin-left: 0; /* No margin when sidebar hidden */
                width: 100%; /* Full width for content */
                padding-top: 4.5rem; /* Adjust padding for top bar height */
            }
            .top-bar {
                left: 0;
                width: 100%;
            }
            .sidebar-toggler {
                display: none; /* Hide desktop toggler on mobile */
            }
            .mobile-sidebar-close-btn {
                display: block; /* Show mobile close button */
            }
            .navbar-toggler.mobile-menu-toggler { /* Your existing mobile toggler */
                display: block !important;
                margin-right: 1rem; /* Space from logo */
                color: var(--primary-text-color); /* Make it visible on yellow background */
            }
             .top-bar .breadcrumb {
                font-size: 1rem; /* Smaller font on mobile */
             }
        }
        @media (min-width: 992px) { /* On desktop, hide mobile toggler */
            .navbar-toggler.mobile-menu-toggler {
                display: none !important;
            }
        }

        /* === TEXT COLOR OVERRIDES - ENSURE DARK TEXT ON LIGHT BACKGROUNDS === */
        
        /* Global text color - dark on light backgrounds */
        body,
        p,
        span,
        div,
        label,
        a,
        h1, h2, h3, h4, h5, h6,
        .text-dark,
        .content-body,
        .main-content {
            color: var(--text-dark) !important;
        }
        
        /* Keep white text on colored backgrounds */
        .dashboard-card,
        .dashboard-card *,
        .bg-color-1,
        .bg-color-2,
        .bg-color-3,
        .bg-color-4,
        .bg-color-5,
        .bg-color-6,
        .badge,
        .btn-primary,
        .sidebar-nav .nav-item .nav-link.active,
        .sidebar-nav .nav-item .nav-link.active * {
            color: #FFFFFF !important;
        }
        
        /* Tables - dark text */
        table,
        .table,
        .table td,
        .table th,
        .table tbody,
        .table thead {
            color: var(--text-dark) !important;
        }
        
        /* Cards - dark text */
        .card,
        .card-body,
        .card-title,
        .card-text,
        .box {
            color: var(--text-dark) !important;
        }
        
        /* Forms - dark text */
        .form-control,
        .form-label,
        .form-text,
        input,
        textarea,
        select {
            color: var(--text-dark) !important;
        }
        
        /* Links - orange */
        a:not(.nav-link):not(.btn):not(.dropdown-item) {
            color: var(--primary-orange) !important;
        }
        
        /* Sidebar text - dark except active */
        .sidebar-nav .nav-item .nav-link:not(.active) {
            color: var(--text-dark) !important;
        }
        
        /* Top bar text - dark */
        .top-bar,
        .top-bar *:not(.dropdown-menu):not(.dropdown-item) {
            color: var(--text-dark) !important;
        }
        
        /* Breadcrumb - orange */
        .breadcrumb,
        .breadcrumb-item {
            color: var(--primary-orange) !important;
        }

        /* Utility classes for consistency */
        .font-weight-medium { font-weight: 500; }
        .font-weight-semibold { font-weight: 600; }
        .opacity-75 { opacity: 0.75; }

        /* ORANGE, CREAM & WHITE THEME - GLOBAL OVERRIDES FOR ALL PAGES */
        .card, .box {
            background-color: var(--white) !important;
            border: 1px solid rgba(241, 90, 34, 0.2) !important;
            color: var(--text-dark) !important;
        }

        .card-header {
            background-color: var(--cream) !important;
            border-bottom: 2px solid var(--primary-orange) !important;
            color: var(--primary-orange) !important;
            font-weight: 700 !important;
        }

        .card-header h5, .card-header h4, .card-header h3 {
            color: var(--primary-orange) !important;
            font-weight: 700 !important;
        }

        .table {
            color: var(--text-dark) !important;
            background-color: transparent !important;
        }

        .table thead th {
            background-color: var(--cream) !important;
            color: var(--primary-orange) !important;
            font-weight: 700 !important;
            border-color: var(--primary-orange) !important;
        }

        .table tbody td {
            border-color: rgba(241, 90, 34, 0.1) !important;
            color: var(--text-dark) !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(241, 90, 34, 0.03) !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(241, 90, 34, 0.08) !important;
        }

        .badge-success { background-color: var(--primary-orange) !important; color: white !important; }
        .badge-danger { background-color: #DC2626 !important; color: white !important; }
        .badge-warning { background-color: #F59E0B !important; color: white !important; }
        .badge-info { background-color: var(--primary-orange) !important; color: white !important; }

        .btn-primary {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
            color: white !important;
        }

        .btn-primary:hover {
            background-color: var(--dark-orange) !important;
            border-color: var(--dark-orange) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(241, 90, 34, 0.3) !important;
        }

        .btn-outline-secondary {
            border: 2px solid var(--primary-orange) !important;
            color: var(--primary-orange) !important;
            background-color: white !important;
        }

        .btn-outline-secondary:hover {
            background-color: var(--primary-orange) !important;
            color: white !important;
        }

        .form-control, .form-select {
            background-color: var(--white) !important;
            border: 2px solid rgba(241, 90, 34, 0.3) !important;
            color: var(--text-dark) !important;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--white) !important;
            border-color: var(--primary-orange) !important;
            color: var(--text-dark) !important;
            box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25) !important;
        }

        .alert-info {
            background-color: rgba(241, 90, 34, 0.1) !important;
            border-color: var(--primary-orange) !important;
            color: var(--text-dark) !important;
        }

        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1) !important;
            border-color: #F59E0B !important;
            color: var(--text-dark) !important;
        }

        pre, code {
            background-color: var(--cream) !important;
            color: var(--primary-orange) !important;
            border: 1px solid rgba(241, 90, 34, 0.2) !important;
        }

        h1, h2, h3, h4, h5, h6, p {
            color: var(--text-dark) !important;
        }

        a:not(.btn) {
            color: var(--primary-orange) !important;
        }

        a:not(.btn):hover {
            color: var(--dark-orange) !important;
        }

        /* DataTables overrides */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--primary-orange) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-orange) !important;
            color: white !important;
            border-color: var(--primary-orange) !important;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background-color: var(--medium-gray) !important;
            border-color: var(--primary-orange) !important;
            color: var(--primary-text-color) !important;
        }

        .dataTables_wrapper .dataTables_info {
            color: var(--primary-text-color) !important;
        }

        /* Modal overrides */
        .modal-content {
            background-color: var(--dark-gray) !important;
            border: 1px solid var(--primary-orange) !important;
        }

        .modal-header {
            background-color: var(--black) !important;
            border-bottom-color: var(--primary-orange) !important;
        }

        .modal-footer {
            border-top-color: var(--medium-gray) !important;
        }
    </style>

    @yield('css')
</head>

<body>
    <div class="wrapper">
        {{-- Sidebar --}}
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/dashboard" class="d-flex align-items-center justify-content-center">
                    <img alt="{{ setting('app_name') }}-logo" class="sidebar-logo" src="/assets-home/images/logo/logo.jpeg" />
                </a>
                {{-- Desktop sidebar toggler --}}
                <button type="button" id="sidebarToggler" class="sidebar-toggler d-none d-lg-block">
                    <i data-lucide="chevrons-left"></i>
                </button>
                {{-- Mobile sidebar close button (internal) --}}
                <button type="button" id="mobileSidebarCloseBtn" class="mobile-sidebar-close-btn d-lg-none">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <ul class="sidebar-nav nav flex-column">
                @if(session()->has('adminlogin'))
                <li class="nav-item">
                    <a href="/admin/userlist" class="nav-link">
                        <i data-lucide="corner-down-right" class="menu-icon"></i>
                        <span class="menu-title">Back to admin</span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link active">
                        <i data-lucide="home" class="menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <!--<li class="nav-item">-->
                <!--    <a class="nav-link collapsed" data-toggle="collapse" href="#profileMenu" role="button" aria-expanded="false" aria-controls="profileMenu">-->
                <!--        <i data-lucide="user-check" class="menu-icon"></i>-->
                <!--        <span class="menu-title">Profile</span>-->
                <!--        <i data-lucide="chevron-down" class="dropdown-arrow"></i>-->
                <!--    </a>-->
                <!--    <div class="collapse" id="profileMenu" data-parent="#sidebar">-->
                <!--        <ul class="nav flex-column sub-menu">-->
                <!--            <li class="nav-item"><a class="nav-link" href="{{ url('user/edit-profile') }}"><i data-lucide="corner-down-right" class="menu-icon"></i>Edit Profile</a></li>-->
                <!--            <li class="nav-item"><a class="nav-link" href="{{ url('user/reset-password') }}"><i data-lucide="corner-down-right" class="menu-icon"></i>Reset Password</a></li>-->
                <!--            <li class="nav-item"><a class="nav-link" href="{{ url('user/trans-password') }}"><i data-lucide="corner-down-right" class="menu-icon"></i>Transaction Password</a></li>-->
                <!--        </ul>-->
                <!--    </div>-->
                <!--</li>-->
                <li class="nav-item">
                    <a href="/user/add-fund-history" class="nav-link">
                        <i data-lucide="book" class="menu-icon"></i>
                        <span class="menu-title">Wallet Fund History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/user/payin_report" class="nav-link">
                        <i data-lucide="bar-chart-2" class="menu-icon"></i>
                        <span class="menu-title">PayIn Report</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/user/payout_report" class="nav-link">
                        <i data-lucide="pie-chart" class="menu-icon"></i>
                        <span class="menu-title">PayOut Report</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/user/settlement_report" class="nav-link">
                        <i data-lucide="dollar-sign" class="menu-icon"></i>
                        <span class="menu-title">Settlement Report</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/user/payment-link" class="nav-link">
                        <i data-lucide="link" class="menu-icon"></i>
                        <span class="menu-title">Payment Link</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/user/pgdocs" class="nav-link" target="_blank">
                        <i data-lucide="file-text" class="menu-icon"></i>
                        <span class="menu-title">Gateway Docs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('user/reset-password') }}">
                        <i data-lucide="lock" class="menu-icon"></i>
                        <span class="menu-title">Reset Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/user/support/chat-support">
                        <i data-lucide="headphones" class="menu-icon"></i>
                        <span class="menu-title">Support</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('logout') }}" class="nav-link">
                        <i data-lucide="log-out" class="menu-icon"></i>
                        <span class="menu-title">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        {{-- Main Content Area --}}
        <div class="main-content" id="main-content" style="overflow:auto">
            {{-- Top Bar / Navbar --}}
            <nav class="top-bar navbar navbar-expand-lg">
                <div class="container-fluid d-flex justify-content-between align-items-center h-100"> {{-- Height 100% --}}
                    {{-- Mobile Toggler for Sidebar (d-lg-none ensures it's only on small screens) --}}
                    <button class="navbar-toggler mobile-menu-toggler d-lg-none" type="button" id="sidebarToggleMobile">
                        <span class="navbar-toggler-icon"><i data-lucide="menu" class="w-6 h-6"></i></span>
                    </button>

                    {{-- Breadcrumb / Page Title --}}
                    <div class="welcome-title d-none d-xl-flex align-items-center"> {{-- d-xl-flex to hide on smaller desktops --}}
                        Welcome to {{ strtoupper(setting('app_name')) }}
                    </div>

                    {{-- Right Side: User Dropdown --}}
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown user-menu-dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img alt="user-image" class="rounded-circle mr-2" style="width: 40px; height: 40px;" src="/assets/img/mothersolution.png" />
                                <div class="d-none d-md-block"> {{-- Hide user name/role on extra small screens --}}
                                    <div class="user-name">{{ user('name') }}</div>
                                    <div class="user-role">Merchant</div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                                <a class="dropdown-item" href="{{ url('user/edit-profile') }}">
                                    <i data-lucide="user" class="mr-2"></i> Profile
                                </a>
                                <a class="dropdown-item" href="{{ url('user/reset-password') }}">
                                    <i data-lucide="lock" class="mr-2"></i> Reset Password
                                </a>
                                <a class="dropdown-item" href="{{ url('user/support/chat-support') }}">
                                    <i data-lucide="headphones" class="mr-2"></i> Support
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ url('logout') }}">
                                    <i data-lucide="log-out" class="mr-2"></i> Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            {{-- Page Specific Content --}}
            <div class="content-body"> {{-- Wrapper for actual page content --}}
                @yield('content')
            </div>
        </div>
    </div>

    {{-- Core JavaScripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
        integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-FxH8tP3Kj9/QfL1Z1H9g2L8t4WdKjJ6U1Q2p4Z5g5L5z5q5V5v5W5v5z5W5" crossorigin="anonymous"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>


    {{-- Third-party Libraries --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"
        integrity="sha512-rstIgDs0xPgmG6RX1Aba4KV5cWJbAMcvRCVmglpam9SoHZiUCyQVDdH2LPlxoHtrv17XWblE/V/PP+Tr04hbtA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="/admin_assets/vendors/sweetalert2/sweetalert2.min.js"></script>
    <script src="/admin_assets/js/sweet-alert.js"></script>
    <script src="/admin_assets/js/main.js"></script>
    
    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        lucide.createIcons(); // Initialize Lucide icons
    </script>

    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>
    
    {{-- Custom Layout JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const topBar = document.querySelector('.top-bar');
            
            // Desktop Sidebar Toggler
            const sidebarToggler = document.getElementById('sidebarToggler');
            if (sidebarToggler) {
                sidebarToggler.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    topBar.classList.toggle('expanded');
                    
                    const icon = sidebarToggler.querySelector('i');
                    if (sidebar.classList.contains('collapsed')) {
                        icon.setAttribute('data-lucide', 'chevrons-right');
                    } else {
                        icon.setAttribute('data-lucide', 'chevrons-left');
                    }
                    lucide.createIcons(); // Re-render icon after attribute change
                });
            }

            // Mobile Sidebar Toggler (from top bar)
            const mobileSidebarToggler = document.getElementById('sidebarToggleMobile');
            if (mobileSidebarToggler) {
                mobileSidebarToggler.addEventListener('click', function() {
                    sidebar.classList.add('active'); // Show sidebar
                    // You might want to add an overlay here to disable content interaction
                    document.body.classList.add('overflow-hidden'); // Prevent scrolling body when sidebar is open
                });
            }

            // Mobile Sidebar Close Button (inside sidebar)
            const mobileSidebarCloseBtn = document.getElementById('mobileSidebarCloseBtn');
            if (mobileSidebarCloseBtn) {
                mobileSidebarCloseBtn.addEventListener('click', function() {
                    sidebar.classList.remove('active'); // Hide sidebar
                    document.body.classList.remove('overflow-hidden'); // Restore scrolling
                });
            }

            // Close sidebar when clicking outside on mobile (if an overlay isn't used)
            // This is a simpler version, a proper overlay is often better for UX
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 992 && sidebar.classList.contains('active')) {
                    if (!sidebar.contains(event.target) && !mobileSidebarToggler.contains(event.target)) {
                        sidebar.classList.remove('active');
                        document.body.classList.remove('overflow-hidden');
                    }
                }
            });
        });

        // Global DataTable initialization function
        function initDataTable(selector = 'table', options = {}) {
            if ($.fn.DataTable.isDataTable(selector)) {
                $(selector).DataTable().destroy();
            }
            new DataTable(selector, {
                layout: {
                    topStart: {
                        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                    }
                },
                ...options // Merge custom options
            });
        }
        
        // Global copy to clipboard function with SweetAlert2
        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Content copied to clipboard.',
                timer: 1500,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-custom-popup', // Add a custom class if you want to style the Swal popup
                    title: 'swal2-custom-title'
                }
            });
        }
    </script>

    @yield('js')
</body>

</html>


    @yield('js')
</body>

</html>
