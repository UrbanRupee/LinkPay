<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="{{ setting('app_name') }} - Admin Panel">
    <meta name="author" content="Adarsh Pushpendra Pandey">
    <title>{{ setting('app_name') }} - Admin Panel</title>

    <!-- Favicon - Using the common path for consistency -->
    <link rel="shortcut icon" href="/assets-home/images/logo/logo.jpeg" />

    <!-- Google Fonts (Inter - for consistency across app, will override Roboto where specified) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Original Core CSS (NobleUI) -->
    <link rel="stylesheet" href="/admin_assets/vendors/core/core.css">

    <!-- Original Plugin CSS for this page -->
    <link rel="stylesheet" href="/admin_assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="/admin_assets/vendors/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/pickr/themes/classic.min.css">

    <!-- Original Inject CSS -->
    <link rel="stylesheet" href="/admin_assets/fonts/feather-font/css/iconfont.css">
    <link rel="stylesheet" href="/admin_assets/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/jquery-tags-input/jquery.tagsinput.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/dropzone/dropzone.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/dropify/dist/dropify.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/admin_assets/vendors/flatpickr/flatpickr.min.css">

    <!-- Original Layout styles (NobleUI's default theme) -->
    <link rel="stylesheet" href="/admin_assets/css/demo1/style.css">

    <!-- Custom Admin Styles (Overrides) -->
    <style>
        :root {
            /* NEW ORANGE THEME */
            --primary-orange: #F15A22;
            --light-cream: #FFEDD5;
            --white: #FFFFFF;
            --text-dark: #1F2937;
            --text-light: #6B7280;
            
            --primary-admin-color: #F15A22;
            --secondary-admin-color: #6c757d;
            
            /* Orange Sidebar Colors */
            --sidebar-admin-bg: #FFFFFF;
            --sidebar-admin-text: #1F2937;
            --sidebar-admin-hover-bg: rgba(241, 90, 34, 0.1);
            --sidebar-admin-active-bg: #F15A22;
            --sidebar-admin-active-text: #FFFFFF;

            --navbar-admin-bg: #FFFFFF;
            --page-bg: #FFEDD5;
            --text-color-dark: #1F2937;
            --text-color-light: #6B7280;
            --card-bg: #FFFFFF;
            --border-radius-md: 0.5rem;
            --shadow-sm: 0 2px 8px rgba(241, 90, 34, 0.1);
            --shadow-md: 0 6px 20px rgba(241, 90, 34, 0.15);
        }

        /* --- Global Overrides - LOCK HORIZONTAL SCROLL --- */
        html {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            width: 100% !important;
            max-width: 100vw !important;
        }
        
        body {
            font-family: 'Inter', sans-serif !important; /* Force Inter font */
            background-color: var(--page-bg) !important;
            color: var(--text-color-dark) !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            width: 100% !important;
            max-width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .main-wrapper {
            background-color: var(--page-bg);
            overflow-x: hidden !important;
            overflow-y: auto !important;
            width: 100% !important;
            max-width: 100vw !important;
            min-height: 100vh !important;
        }
        
        /* Lock page content area */
        .page-wrapper {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }
        
        .page-content {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }

        /* --- Sidebar Overrides - WHITE SIDEBAR - FULLY LOCKED --- */
        .sidebar {
            background-color: #FFFFFF !important;
            background: #FFFFFF !important;
            border-right: 3px solid var(--primary-orange) !important;
            box-shadow: 4px 0 20px rgba(241, 90, 34, 0.1) !important;
            transition: width 0.3s ease !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
            display: flex !important;
            flex-direction: column !important;
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            width: 250px !important;
            height: 100vh !important;
            z-index: 999 !important;
            transform: translateX(0) !important;
        }
        
        /* Prevent sidebar from moving */
        .sidebar,
        .sidebar-offcanvas {
            transform: none !important;
            -webkit-transform: none !important;
            will-change: auto !important;
        }
        
        /* Force white on sidebar body and all children */
        .sidebar,
        .sidebar .sidebar-body,
        .sidebar .sidebar-header,
        .sidebar-offcanvas {
            background-color: #FFFFFF !important;
            background: #FFFFFF !important;
        }
        
        /* Style for when the sidebar is collapsed (NobleUI often uses .sidebar-folded on body) */
        body.sidebar-folded .sidebar {
            width: 80px !important;
            background-color: #FFFFFF !important;
        }

        .sidebar .sidebar-header {
            background-color: #FFFFFF !important;
            border-bottom: 3px solid var(--primary-orange) !important;
            padding: 1rem !important;
            text-align: center !important;
            margin-bottom: 0 !important;
            position: relative !important;
            flex-shrink: 0 !important;
            height: 90px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .sidebar .sidebar-brand {
            background-color: transparent !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }
        
        .sidebar .sidebar-brand img,
        .sidebar .sidebar-logo {
            max-width: 180px !important;
            max-height: 60px !important;
            width: auto !important;
            height: auto !important;
            display: block !important;
            object-fit: contain !important;
        }
        
        body.sidebar-folded .sidebar .sidebar-brand img,
        body.sidebar-folded .sidebar .sidebar-logo {
            max-width: 55px !important;
            max-height: 55px !important;
        }
        
        body.sidebar-folded .sidebar .sidebar-header {
            padding: 0.5rem !important;
            height: 70px !important;
        }
        
        body.sidebar-folded .sidebar .sidebar-body {
            height: calc(100vh - 70px) !important;
        }
        
        /* Ensure sidebar body doesn't overlap and has scrollbar - LOCK CONTENT */
        .sidebar .sidebar-body {
            padding: 1rem 0 1rem 0 !important;
            margin: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            flex: 1 !important;
            height: calc(100vh - 90px) !important;
            width: 100% !important;
            max-width: 250px !important;
        }
        
        /* Lock all sidebar content - prevent horizontal movement */
        .sidebar .sidebar-body * {
            max-width: 100% !important;
            overflow-x: hidden !important;
            white-space: normal !important;
            word-wrap: break-word !important;
        }
        
        /* Lock menu items with proper spacing */
        .sidebar .nav {
            max-width: 100% !important;
            overflow-x: hidden !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .sidebar .nav-item {
            max-width: 100% !important;
            overflow-x: hidden !important;
            margin-bottom: 2px !important;
        }
        
        .sidebar .nav-link {
            max-width: 100% !important;
            overflow-x: hidden !important;
            white-space: nowrap !important;
            text-overflow: ellipsis !important;
            padding: 12px 20px !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }
        
        /* Sidebar sub-menu spacing */
        .sidebar .nav-item .collapse,
        .sidebar .nav-item .collapsing {
            padding-left: 0 !important;
            margin-left: 0 !important;
        }
        
        .sidebar .nav-item .collapse .nav-link,
        .sidebar .nav-item .collapsing .nav-link {
            padding: 10px 15px 10px 45px !important;
            font-size: 13px !important;
        }
        
        /* Menu category headers - VISIBLE AND CLEAR */
        .sidebar .sidebar-heading {
            padding: 18px 20px 10px 20px !important;
            margin: 15px 0 8px 0 !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.8px !important;
            color: #6B7280 !important;
            line-height: 1.5 !important;
            height: auto !important;
            min-height: 40px !important;
            display: block !important;
            overflow: visible !important;
            white-space: normal !important;
            word-wrap: break-word !important;
        }
        
        /* Menu icons */
        .sidebar .nav-link i,
        .sidebar .nav-link svg {
            flex-shrink: 0 !important;
            width: 18px !important;
            height: 18px !important;
            margin-right: 10px !important;
        }
        
        /* Menu text */
        .sidebar .nav-link .link-title {
            flex: 1 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        
        /* Custom scrollbar for sidebar */
        .sidebar .sidebar-body::-webkit-scrollbar {
            width: 6px !important;
        }
        
        .sidebar .sidebar-body::-webkit-scrollbar-track {
            background: rgba(241, 90, 34, 0.05) !important;
        }
        
        .sidebar .sidebar-body::-webkit-scrollbar-thumb {
            background: var(--primary-orange) !important;
            border-radius: 3px !important;
        }
        
        .sidebar .sidebar-body::-webkit-scrollbar-thumb:hover {
            background: var(--dark-orange) !important;
        }
        
        .sidebar .sidebar-body .nav {
            margin-top: 0 !important;
            padding: 0 1rem 1rem 1rem !important;
        }
        
        /* First nav item spacing */
        .sidebar .sidebar-body .nav .nav-item:first-child,
        .sidebar .sidebar-body .nav .nav-category:first-child {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .sidebar .sidebar-toggler {
            position: absolute !important;
            right: 0.5rem !important;
            top: 1rem !important;
            color: var(--primary-orange) !important;
            opacity: 0.8 !important;
            transition: opacity 0.2s ease !important;
            cursor: pointer !important;
            z-index: 100 !important;
            width: 28px !important;
            height: 28px !important;
        }
        
        .sidebar .sidebar-toggler:hover {
            opacity: 1 !important;
        }
        
        /* Active state for toggler */
        .sidebar .sidebar-toggler.not-active span {
            background-color: var(--primary-orange) !important;
        }
        
        .sidebar .sidebar-toggler span {
            background-color: var(--primary-orange) !important;
            display: block !important;
            height: 2px !important;
            width: 100% !important;
            margin-bottom: 5px !important;
        }


        .sidebar .nav-item .nav-link {
            color: var(--sidebar-admin-text) !important;
            border-radius: var(--border-radius-md) !important;
            margin: 0.25rem 0.75rem !important;
            padding: 0.75rem 1.25rem !important;
            transition: all 0.2s ease !important;
        }
        .sidebar .nav-item .nav-link:hover {
            background-color: var(--sidebar-admin-hover-bg) !important;
            color: #fff !important;
        }
        .sidebar .nav-item.active > .nav-link,
        .sidebar .nav-item.active > .nav-link:hover,
        .sidebar .nav-item .nav-link.active {
            background-color: var(--sidebar-admin-active-bg) !important;
            color: var(--sidebar-admin-active-text) !important;
            font-weight: 500 !important;
        }
        .sidebar .nav-item .nav-link i {
            color: var(--primary-orange) !important;
            margin-right: 0.75rem !important;
        }
        .sidebar .nav-item.active > .nav-link i,
        .sidebar .nav-item .nav-link.active i {
            color: var(--white) !important; /* White icon for active state */
        }
        .sidebar .nav-item .nav-link:hover i {
            color: var(--primary-orange) !important;
        }

        /* Dropdown arrow specific styles */
        .sidebar .nav-item .nav-link[data-bs-toggle="collapse"]::after,
        .sidebar .nav-item .nav-link[data-toggle="collapse"]::after {
            color: var(--sidebar-admin-text) !important;
            position: absolute !important; /* Ensure it's positioned correctly */
            right: 1.25rem !important;
            top: 50% !important;
            transform: translateY(-50%) rotate(0deg) !important;
            transition: transform 0.2s ease !important;
            font-family: 'feather' !important; /* Use feather font for chevron */
            content: "\e843" !important; /* feather chevron-right */
        }
        .sidebar .nav-item .nav-link[aria-expanded="true"]::after {
            transform: translateY(-50%) rotate(90deg) !important; /* Rotate on expand */
        }
        /* Hide arrows when sidebar is folded */
        body.sidebar-folded .sidebar .link-arrow {
            display: none !important;
        }

        /* Sub-menu styling */
        .sidebar .nav-item .nav-link[data-bs-toggle="collapse"][aria-expanded="true"] + .collapse,
        .sidebar .nav-item .nav-link[data-toggle="collapse"][aria-expanded="true"] + .collapse {
            background-color: rgba(0, 0, 0, 0.15) !important;
            border-radius: var(--border-radius-md) !important;
            margin: 0.25rem 0.75rem !important;
        }
        .sidebar .nav-item .nav-link[data-bs-toggle="collapse"] + .collapse .nav-link,
        .sidebar .nav-item .nav-link[data-toggle="collapse"] + .collapse .nav-link {
            padding-left: 2.5rem !important;
            font-size: 0.9rem !important;
            color: var(--sidebar-admin-text) !important;
        }
        .sidebar .nav-item .nav-link[data-bs-toggle="collapse"] + .collapse .nav-link:hover,
        .sidebar .nav-item .nav-link[data-toggle="collapse"] + .collapse .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        .sidebar .nav-item .nav-link[data-bs-toggle="collapse"] + .collapse .nav-item.active .nav-link,
        .sidebar .nav-item .nav-link[data-toggle="collapse"] + .collapse .nav-item.active .nav-link {
            background-color: rgba(0, 0, 0, 0.2) !important; /* Deeper active for sub-items */
            color: #fff !important;
        }
        .sidebar .nav-item .nav-link[data-bs-toggle="collapse"] + .collapse .nav-link i,
        .sidebar .nav-item .nav-link[data-toggle="collapse"] + .collapse .nav-link i {
            color: var(--sidebar-admin-text) !important; /* Submenu icon color */
            margin-right: 0.5rem !important;
            width: 18px !important; height: 18px !important;
        }
        .sidebar .nav-category {
            color: var(--primary-orange) !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            font-weight: 700 !important;
            padding: 0.75rem 1.5rem !important;
            margin-top: 1.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        /* First category no top margin */
        .sidebar .nav-category:first-child {
            margin-top: 0 !important;
        }


        /* --- Navbar Overrides - Orange Theme --- */
        .navbar {
            background-color: var(--navbar-admin-bg) !important;
            border-bottom: 3px solid var(--primary-orange) !important;
            box-shadow: 0 4px 12px rgba(241, 90, 34, 0.1) !important;
        }
        .navbar .navbar-brand {
            color: var(--text-color-dark) !important;
        }
        .navbar .nav-link {
            color: var(--text-color-dark) !important;
        }
        .navbar .profile-pic {
            border: 2px solid rgba(0, 0, 0, 0.1) !important;
            box-shadow: none !important;
        }
        /* Mobile menu toggler on navbar */
        .navbar .sidebar-toggler {
            color: var(--text-color-dark) !important; /* Ensure it's visible on white navbar */
        }


        /* --- Page Content Area Overrides --- */
        .page-wrapper {
            background-color: var(--page-bg) !important;
            transition: margin-left 0.3s ease !important; /* For content shift when sidebar folds */
        }
        body.sidebar-folded .page-wrapper {
            margin-left: 80px !important; /* Adjust based on collapsed sidebar width */
        }
        .page-content {
            padding: 1.5rem !important;
        }
        
        /* --- Card Overrides - Orange Theme --- */
        .card {
            background-color: var(--card-bg) !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-sm) !important;
            border: 1px solid rgba(241, 90, 34, 0.2) !important;
            margin-bottom: 1.5rem !important;
        }
        .card-header {
            background-color: var(--light-cream) !important;
            border-bottom: 2px solid var(--primary-orange) !important;
            font-weight: 700 !important;
            color: var(--primary-orange) !important;
            padding: 1rem 1.5rem !important;
        }
        .card-body {
            padding: 1.5rem !important;
        }

        /* --- Table Overrides --- */
        .table {
            width: 100% !important;
            margin-bottom: 0 !important;
        }
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .table thead th {
            background-color: var(--light-cream) !important;
            color: var(--primary-orange) !important;
            font-weight: 700 !important;
            border-bottom: 2px solid var(--primary-orange) !important;
            vertical-align: middle !important;
            padding: 0.85rem 1.25rem !important;
            white-space: nowrap !important;
            text-align: left !important;
        }
        .table tbody td {
            vertical-align: middle !important;
            padding: 0.75rem 1.25rem !important;
            white-space: nowrap !important;
            max-width: 250px !important;
            /*overflow: hidden !important;*/
            /*text-overflow: ellipsis !important;*/
        }

        /* --- Form Elements Overrides - Orange Theme --- */
        .form-control {
            border-radius: var(--border-radius-md) !important;
            padding: 0.75rem 1rem !important;
            border: 2px solid rgba(241, 90, 34, 0.3) !important;
        }
        .form-control:focus {
            border-color: var(--primary-orange) !important;
            box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25) !important;
        }
        .form-label, label {
            font-weight: 600 !important;
            color: var(--text-dark) !important;
            margin-bottom: 0.5rem !important;
            display: block !important;
        }

        /* --- Button Overrides - Orange Theme --- */
        .btn {
            border-radius: 8px !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }
        .btn-primary {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
            color: #FFFFFF !important;
        }
        .btn-primary:hover {
            background-color: #D14A15 !important;
            border-color: #D14A15 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(241, 90, 34, 0.3) !important;
        }
        .btn-secondary {
            background-color: var(--white) !important;
            border: 2px solid var(--primary-orange) !important;
            color: var(--primary-orange) !important;
        }
        .btn-secondary:hover {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }

        /* Badge Styles - Orange Theme */
        .badge {
            font-weight: 600 !important;
            padding: 0.35rem 0.65rem !important;
            border-radius: 6px !important;
        }
        .badge-primary, .badge.bg-primary {
            background-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }
        .badge-success, .badge.bg-success {
            background-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }

        /* SweetAlert2 styling - Orange Theme */
        .swal2-popup {
            border-radius: 12px !important;
            font-family: 'Inter', sans-serif !important;
            border: 2px solid var(--primary-orange) !important;
        }
        .swal2-confirm {
            background-color: var(--primary-orange) !important;
            border: none !important;
        }
        .swal2-confirm:hover {
            background-color: #D14A15 !important;
        }
        /* Mobile navbar toggler styling (NobleUI has one, ensure it's visible) */
        .navbar .sidebar-toggler {
            font-size: 1.5rem !important;
            color: var(--text-color-dark) !important;
        }

        /* --- Theme Switcher Sidebar (settings-sidebar) --- */
        .settings-sidebar {
            background-color: #333 !important; /* Darker background for settings sidebar */
            color: #fff !important;
            border-left: 1px solid rgba(255,255,255,0.1) !important;
        }
        .settings-sidebar .sidebar-body h6,
        .settings-sidebar .sidebar-body label {
            color: #fff !important;
        }
        .settings-sidebar .form-check-input:checked + .form-check-label::before {
            background-color: var(--primary-admin-color) !important;
            border-color: var(--primary-admin-color) !important;
        }
        
        /* Class applied by NobleUI's settings sidebar JS - WHITE SIDEBAR */
        .sidebar-light .sidebar {
            background-color: #FFFFFF !important;
            background: #FFFFFF !important;
            border-right: 3px solid var(--primary-orange) !important;
            box-shadow: 4px 0 20px rgba(241, 90, 34, 0.1) !important;
        }
        .sidebar-light .sidebar .sidebar-body,
        .sidebar-light .sidebar .sidebar-header {
            background-color: #FFFFFF !important;
            background: #FFFFFF !important;
        }
        .sidebar-light .sidebar .nav-item .nav-link {
            color: var(--text-dark) !important;
        }
        .sidebar-light .sidebar .nav-item .nav-link:hover {
            background-color: var(--sidebar-admin-hover-bg) !important;
            color: var(--text-dark) !important;
        }
        .sidebar-light .sidebar .nav-item.active > .nav-link,
        .sidebar-light .sidebar .nav-item .nav-link.active {
            background-color: var(--primary-orange) !important;
            color: var(--white) !important;
            font-weight: 600 !important;
        }
        .sidebar-light .sidebar .nav-item .nav-link i,
        .sidebar-light .sidebar .nav-item .nav-link[data-bs-toggle="collapse"]::after,
        .sidebar-light .sidebar .nav-item .nav-link[data-toggle="collapse"]::after {
            color: var(--primary-orange) !important;
        }
        .sidebar-light .sidebar .nav-item.active > .nav-link i,
        .sidebar-light .sidebar .nav-item .nav-link.active i {
            color: var(--white) !important;
        }
        .sidebar-light .sidebar .nav-item .nav-link:hover i {
            color: var(--primary-orange) !important;
        }
        .sidebar-light .sidebar .nav-item .nav-link[aria-expanded="true"] + .collapse {
            background-color: rgba(241, 90, 34, 0.05) !important;
        }
        .sidebar-light .sidebar .nav-category {
            color: var(--primary-orange) !important;
            font-weight: 700 !important;
        }
        .sidebar-light .sidebar .sidebar-brand {
            border-bottom: 3px solid var(--primary-orange) !important;
        }
        
        .sidebar .sidebar-body .nav {
            padding: 0px;
        }
        .main-wrapper .page-wrapper .page-content {
            margin-top: 10px;
        }

        /* ========== COMPREHENSIVE ORANGE OVERRIDE - FORCE ALL BLUES TO ORANGE ========== */
        
        /* FORCE WHITE SIDEBAR - Override everything */
        .sidebar,
        .sidebar *:not(.nav-link.active):not(.nav-link.active *),
        body .sidebar,
        html body .sidebar,
        .main-wrapper .sidebar,
        .sidebar .sidebar-body,
        .sidebar .sidebar-header,
        .sidebar-light .sidebar,
        body.sidebar-dark .sidebar,
        body.sidebar-folded .sidebar {
            background-color: #FFFFFF !important;
            background: #FFFFFF !important;
        }
        
        /* Override ANY primary color from NobleUI/Bootstrap */
        .bg-primary, .badge-primary, .btn-primary, .text-primary,
        .border-primary, .alert-primary, .list-group-item-primary {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }

        /* Override link colors */
        a, .text-primary, .link-primary {
            color: var(--primary-orange) !important;
        }
        a:hover, .text-primary:hover {
            color: #D14A15 !important;
        }

        /* Override active/focus states */
        .nav-link.active,
        .nav-pills .nav-link.active,
        .list-group-item.active,
        .page-link.active,
        .page-item.active .page-link {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }

        /* Override dropdown active items */
        .dropdown-item:active,
        .dropdown-item.active {
            background-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }

        /* Override progress bars */
        .progress-bar {
            background-color: var(--primary-orange) !important;
        }

        /* Override checkboxes and radios when checked */
        .form-check-input:checked {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
        }

        /* Override any remaining text-primary classes */
        .text-primary, h1.text-primary, h2.text-primary, h3.text-primary,
        h4.text-primary, h5.text-primary, h6.text-primary {
            color: var(--primary-orange) !important;
        }

        /* Override datatable buttons if any */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:active {
            background: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }

        /* Override pagination */
        .pagination .page-item.active .page-link {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
        }
        .pagination .page-link:hover {
            background-color: rgba(241, 90, 34, 0.1) !important;
            border-color: var(--primary-orange) !important;
            color: var(--primary-orange) !important;
        }

        /* Override select2 dropdown active items */
        .select2-results__option--highlighted {
            background-color: var(--primary-orange) !important;
        }
        .select2-container--default .select2-results__option--selected {
            background-color: rgba(241, 90, 34, 0.2) !important;
        }

        /* FORCE sidebar link active color - most specific selector */
        .sidebar .sidebar-body .nav .nav-item .nav-link.active,
        .sidebar .sidebar-body .nav .nav-item.active > .nav-link,
        body .sidebar .nav-item.active > .nav-link,
        body .sidebar .nav-item .nav-link.active {
            background-color: var(--primary-orange) !important;
            color: var(--white) !important;
        }

        /* FORCE sidebar icon colors */
        .sidebar .nav-item .nav-link .link-icon,
        .sidebar .nav-item .nav-link i.link-icon,
        .sidebar .nav-item .nav-link i,
        .sidebar .sidebar-body .nav .nav-item .nav-link i {
            color: var(--primary-orange) !important;
        }

        /* FORCE active sidebar icons to white */
        .sidebar .nav-item.active > .nav-link i,
        .sidebar .nav-item .nav-link.active i,
        .sidebar .sidebar-body .nav .nav-item.active > .nav-link i,
        .sidebar .sidebar-body .nav .nav-item .nav-link.active i {
            color: var(--white) !important;
        }

        /* Override any border colors */
        .border, .border-top, .border-bottom, .border-left, .border-right {
            border-color: rgba(241, 90, 34, 0.2) !important;
        }
    </style>
    @yield('css')
</head>

<body>
    <div class="main-wrapper">

        <!-- partial:partials/_sidebar.html -->
        @include('admin.include.sidebar')
        <!-- partial -->

        <div class="page-wrapper">

            <!-- partial:partials/_navbar.html -->
            @include('admin.include.navbar')
            <!-- partial -->

            <div class="page-content">
                @yield('content')
            </div>

            <!-- partial:partials/_footer.html -->
            @include('admin.include.footer')
            <!-- partial -->

        </div>
    </div>

    <!-- Original Core JS -->
    <script src="/admin_assets/vendors/core/core.js"></script>

    <!-- Original Plugin js for this page -->
    <script src="/admin_assets/vendors/flatpickr/flatpickr.min.js"></script>
    <script src="/admin_assets/vendors/apexcharts/apexcharts.min.js"></script>
    <script src="/admin_assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="/admin_assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js"></script>
    
    <!-- Original Plugin JS for other pages (might not be used on every page) -->
    <script src="/admin_assets/vendors/select2/select2.min.js"></script>
    <script src="/admin_assets/vendors/inputmask/jquery.inputmask.min.js"></script>
    <script src="/admin_assets/vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="/admin_assets/vendors/jquery-tags-input/jquery.tagsinput.min.js"></script>
    <script src="/admin_assets/vendors/dropzone/dropzone.min.js"></script>
    <script src="/admin_assets/vendors/dropify/dist/dropify.min.js"></script>
    <script src="/admin_assets/vendors/pickr/pickr.min.js"></script>
    <script src="/admin_assets/vendors/moment/moment.min.js"></script>
    <script src="/admin_assets/vendors/flatpickr/flatpickr.min.js"></script>

    <!-- Original Inject JS -->
    <script src="/admin_assets/vendors/feather-icons/feather.min.js"></script>
    <script src="/admin_assets/js/template.js"></script> {{-- NobleUI's core JS for sidebar/navbar functionality --}}

    <!-- Original Custom js for this page -->
    <script src="/admin_assets/js/main.js"></script>
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

    <script>
        $(document).ready(function() {
            // NobleUI's template.js should handle the main sidebar toggler.
            // If it's not working, you might need to debug template.js or add custom JS:
            // $('.sidebar-toggler').on('click', function() {
            //     $('body').toggleClass('sidebar-folded'); // Or whatever class NobleUI uses
            // });

            // Ensure Feather icons are replaced, as NobleUI uses them
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // --- Interactivity for the Settings Sidebar Theme Switcher ---
            // This assumes NobleUI doesn't handle this exact class toggling
            // It will apply/remove 'sidebar-dark' or 'sidebar-light' class to the 'body' tag
            $('input[name="sidebarThemeSettings"]').on('change', function() {
                const theme = $(this).val(); // 'sidebar-light' or 'sidebar-dark'
                
                // Remove existing sidebar theme classes from body
                $('body').removeClass('sidebar-light sidebar-dark'); 
                
                // Add the selected theme class to body
                $('body').addClass(theme);

                // Optionally, save preference to localStorage
                localStorage.setItem('adminSidebarTheme', theme);
            });

            // Apply saved sidebar theme on load
            const savedTheme = localStorage.getItem('adminSidebarTheme');
            if (savedTheme) {
                $('body').addClass(savedTheme);
                // Also check the corresponding radio button
                $(`input[name="sidebarThemeSettings"][value="${savedTheme}"]`).prop('checked', true);
            } else {
                // Default to dark sidebar if nothing saved (or based on your preference)
                // If you want default to be light, set sidebarLight checked in HTML
                $('body').addClass('sidebar-dark');
                $('#sidebarDark').prop('checked', true);
            }

            // Manual re-initialization of DataTables, if needed
            // If data-table.js initializes globally, ensure it's compatible with these new styles.
            // Otherwise, for pages using DataTables, you'd typically initialize them in yield('js')
            // using the specific id of the table.
        });
    </script>

    <!-- FORCE ORANGE THEME & WHITE SIDEBAR - Remove any blue/dark colors dynamically -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Force orange theme and white sidebar
            const orangeColor = '#F15A22';
            const whiteColor = '#FFFFFF';
            
            // Override any inline styles with blue colors
            function forceOrangeTheme() {
                // ===== LOCK SIDEBAR POSITION - PREVENT ANY MOVEMENT =====
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.style.setProperty('position', 'fixed', 'important');
                    sidebar.style.setProperty('left', '0', 'important');
                    sidebar.style.setProperty('top', '0', 'important');
                    sidebar.style.setProperty('transform', 'none', 'important');
                    sidebar.style.setProperty('-webkit-transform', 'none', 'important');
                    sidebar.style.setProperty('will-change', 'auto', 'important');
                }
                
                // ===== PREVENT HORIZONTAL SCROLL =====
                document.documentElement.style.setProperty('overflow-x', 'hidden', 'important');
                document.body.style.setProperty('overflow-x', 'hidden', 'important');
                
                const mainWrapper = document.querySelector('.main-wrapper');
                if (mainWrapper) {
                    mainWrapper.style.setProperty('overflow-x', 'hidden', 'important');
                }
                
                // ===== FORCE WHITE SIDEBAR =====
                const sidebarElements = document.querySelectorAll('.sidebar, .sidebar .sidebar-body, .sidebar .sidebar-header');
                sidebarElements.forEach(function(el) {
                    if (!el.classList.contains('nav-link') || !el.classList.contains('active')) {
                        el.style.setProperty('background-color', whiteColor, 'important');
                        el.style.setProperty('background', whiteColor, 'important');
                    }
                });
                
                // ===== FORCE BLUE TO ORANGE =====
                const allElements = document.querySelectorAll('*');
                
                allElements.forEach(function(el) {
                    const computedStyle = window.getComputedStyle(el);
                    const bgColor = computedStyle.backgroundColor;
                    const color = computedStyle.color;
                    const borderColor = computedStyle.borderColor;
                    
                    // Check for common blue colors in backgrounds
                    if (bgColor.includes('rgb(0, 123, 255)') || 
                        bgColor.includes('rgb(79, 70, 229)') ||
                        bgColor.includes('rgb(13, 110, 253)') ||
                        bgColor.includes('rgb(0, 91, 187)')) {
                        el.style.setProperty('background-color', orangeColor, 'important');
                        el.style.setProperty('color', whiteColor, 'important');
                    }
                    
                    // Check for blue text colors
                    if (color.includes('rgb(0, 123, 255)') || 
                        color.includes('rgb(79, 70, 229)') ||
                        color.includes('rgb(0, 91, 187)')) {
                        el.style.setProperty('color', orangeColor, 'important');
                    }
                    
                    // Check for blue borders
                    if (borderColor.includes('rgb(0, 123, 255)') || 
                        borderColor.includes('rgb(79, 70, 229)') ||
                        borderColor.includes('rgb(0, 91, 187)')) {
                        el.style.setProperty('border-color', orangeColor, 'important');
                    }
                });
                
                // Specifically target sidebar - FORCE WHITE
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.style.setProperty('background-color', whiteColor, 'important');
                    sidebar.style.setProperty('background', whiteColor, 'important');
                }
                
                // Specifically target sidebar active links - ORANGE
                document.querySelectorAll('.sidebar .nav-link.active').forEach(function(link) {
                    link.style.setProperty('background-color', orangeColor, 'important');
                    link.style.setProperty('color', whiteColor, 'important');
                });
                
                // Target sidebar icons - ORANGE
                document.querySelectorAll('.sidebar .nav-link i').forEach(function(icon) {
                    if (!icon.closest('.nav-link.active')) {
                        icon.style.setProperty('color', orangeColor, 'important');
                    }
                });
                
                // Target active sidebar icons - WHITE
                document.querySelectorAll('.sidebar .nav-link.active i').forEach(function(icon) {
                    icon.style.setProperty('color', whiteColor, 'important');
                });
            }
            
            // Run immediately
            forceOrangeTheme();
            
            // Run again after a short delay to catch any dynamically loaded content
            setTimeout(forceOrangeTheme, 100);
            setTimeout(forceOrangeTheme, 300);
            setTimeout(forceOrangeTheme, 500);
            setTimeout(forceOrangeTheme, 1000);
            
            // Observe DOM changes and reapply
            const observer = new MutationObserver(function(mutations) {
                forceOrangeTheme();
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        });
    </script>

    @yield('js')
</body>

</html>