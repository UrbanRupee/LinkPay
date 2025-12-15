<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Welcome to {{ setting('app_name') }}, Introducing a new technology of making money. It is US based program, which is latest, fully automated and highly secure with latest technology." />
    <title>{{ setting('app_name') }} - Member Panel</title>
    <meta property="og:image" content="{{ url('assets-home/images/logo/logo.jpeg') }}" />
    <meta property="og:site_name" content="{{ setting('app_name') }}" />
    <link rel="icon" type="image/jpeg" href="{{ url('assets-home/images/logo/logo.jpeg') }}">
    
    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Modern Theme -->
    <link href="{{ asset('resources/views/user/layout/modern-theme.css') }}" rel="stylesheet">
    
    <!-- Additional Styles -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.dataTables.css" />
    
    @yield('css')
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-50);
        }
        
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar-container {
            width: 280px;
            background: linear-gradient(180deg, var(--gray-900) 0%, var(--gray-800) 100%);
            border-right: 1px solid var(--gray-700);
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-collapsed {
            width: 80px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content-collapsed {
            margin-left: 80px;
        }
        
        .top-navbar {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 2rem;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .sidebar-container {
                transform: translateX(-100%);
            }
            
            .sidebar-container.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-area {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <div class="sidebar-container" id="sidebar">
            <div class="p-4">
                <!-- Logo -->
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ url('assets-home/images/logo/logo.jpeg') }}" alt="Logo" class="me-3" style="width: 40px; height: 40px; border-radius: 8px;">
                    <div class="sidebar-brand-text">
                        <h5 class="mb-0 text-white">{{ setting('app_name') }}</h5>
                        <small class="text-gray-300">Member Panel</small>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="sidebar-modern">
                    <ul class="nav flex-column">
                        <!-- Main Section -->
                        <li class="nav-item mb-2">
                            <span class="nav-category text-gray-400 text-uppercase small fw-bold px-3 py-2">Main</span>
                        </li>
                        <li class="nav-item">
                            <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                                <i class="fas fa-home"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        
                        <!-- Profile Section -->
                        <li class="nav-item mb-2 mt-4">
                            <span class="nav-category text-gray-400 text-uppercase small fw-bold px-3 py-2">Profile</span>
                        </li>
                        <li class="nav-item">
                            <a href="/user/edit-profile" class="nav-link {{ request()->is('user/edit-profile') ? 'active' : '' }}">
                                <i class="fas fa-user-edit"></i>
                                <span class="nav-text">Edit Profile</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/user/reset-password" class="nav-link {{ request()->is('user/reset-password') ? 'active' : '' }}">
                                <i class="fas fa-lock"></i>
                                <span class="nav-text">Reset Password</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/user/trans-password" class="nav-link {{ request()->is('user/trans-password') ? 'active' : '' }}">
                                <i class="fas fa-key"></i>
                                <span class="nav-text">Transaction Password</span>
                            </a>
                        </li>
                        
                        <!-- Wallet Section -->
                        <li class="nav-item mb-2 mt-4">
                            <span class="nav-category text-gray-400 text-uppercase small fw-bold px-3 py-2">Wallet</span>
                        </li>
                        <li class="nav-item">
                            <a href="/user/add-fund" class="nav-link {{ request()->is('user/add-fund') ? 'active' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span class="nav-text">Add Fund</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/user/main-wallet" class="nav-link {{ request()->is('user/main-wallet') ? 'active' : '' }}">
                                <i class="fas fa-wallet"></i>
                                <span class="nav-text">Main Wallet</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/user/p2p-transfer" class="nav-link {{ request()->is('user/p2p-transfer') ? 'active' : '' }}">
                                <i class="fas fa-exchange-alt"></i>
                                <span class="nav-text">P2P Transfer</span>
                            </a>
                        </li>
                        
                        <!-- Reports Section -->
                        <li class="nav-item mb-2 mt-4">
                            <span class="nav-category text-gray-400 text-uppercase small fw-bold px-3 py-2">Reports</span>
                        </li>
                        <li class="nav-item">
                            <a href="/user/payin_report" class="nav-link {{ request()->is('user/payin_report') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i>
                                <span class="nav-text">Payin Report</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/user/payout_report" class="nav-link {{ request()->is('user/payout_report') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i>
                                <span class="nav-text">Payout Report</span>
                            </a>
                        </li>
                        
                        <!-- Team Section -->
                        <li class="nav-item mb-2 mt-4">
                            <span class="nav-category text-gray-400 text-uppercase small fw-bold px-3 py-2">Team</span>
                        </li>
                        <li class="nav-item">
                            <a href="/user/refferal-team" class="nav-link {{ request()->is('user/refferal-team') ? 'active' : '' }}">
                                <i class="fas fa-users"></i>
                                <span class="nav-text">Referral Team</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/user/tree-view" class="nav-link {{ request()->is('user/tree-view*') ? 'active' : '' }}">
                                <i class="fas fa-sitemap"></i>
                                <span class="nav-text">Tree View</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Navigation -->
            <div class="top-navbar">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-outline-secondary me-3 d-md-none" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <button class="btn btn-outline-secondary me-3 d-none d-md-block" id="sidebarCollapse">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">@yield('page-title', 'Dashboard')</h4>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- Notifications -->
                        <div class="position-relative">
                            <button class="btn btn-outline-secondary position-relative">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    3
                                </span>
                            </button>
                        </div>
                        
                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                                <img src="{{ url('assets-home/images/logo/logo.jpeg') }}" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                <span>{{ user('name') ?? 'User' }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/user/edit-profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="/user/reset-password"><i class="fas fa-lock me-2"></i>Change Password</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area">
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js"></script>
    
    <script>
        // Sidebar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            
            // Mobile sidebar toggle
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Desktop sidebar collapse
            if (sidebarCollapse) {
                sidebarCollapse.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-collapsed');
                    mainContent.classList.toggle('main-content-collapsed');
                });
            }
            
            // Close mobile sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });
        
        // Initialize DataTables with modern styling
        $(document).ready(function() {
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-select');
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>
