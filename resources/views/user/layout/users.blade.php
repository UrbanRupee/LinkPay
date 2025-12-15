<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description"
        content="Welcome to {{ setting('app_name') }}, Introducing a new technology of making money. It is US
    based program, which is latest, fully automated and highly secure with latest technology. Our
    highly educated and skilled Developers are managing and upgrading this Program." />
    <meta name="keywords" content="" />
    <title>{{ setting('app_name') }} Member Panel</title>
    <meta data-react-helmet="true" property="og:image" content="{{ url('assets/favicon.svg') }}" />
    <meta property="og:site_name" content="{{ setting('app_name') }}" />
    <meta property="og:title" content="{{ setting('app_name') }} " />
    <meta property="og:description"
        content="Welcome to {{ setting('app_name') }}, Introducing a new technology of making money. It is US
      based program, which is latest, fully automated and highly secure with latest technology. Our
      highly educated and skilled Developers are managing and upgrading this Program." />
    <link rel="icon" type="image/png" href="{{ url('assets/favicon.svg') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/assets/favicon.svg">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="/assets3/css/app.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"
        integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous" />
    <link rel="stylesheet" href="/admin_assets/vendors/sweetalert2/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
        integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css" />
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.dataTables.css" />
    <style>
        label.error {
            color: red;
        }
        .table {
    width: max-content;
    min-width: 100%;
    margin-bottom: 1rem;
    color: #212529;
}
    </style>
    @yield('css')
</head>

<body class="main">
    <div class="flex">
        <nav class="side-nav">
            <div class="pt-4 mb-4">
                <div class="side-nav__header flex items-center">
                    <a href="" class="intro-x flex items-center">
                        <img alt="{{ setting('app_name') }}-logo" class="side-nav__header__logo" style="width:100%"
                            src="/assets-home/images/logo/logo.png" />
                    </a>
                    <a href="javascript:;"
                        class="side-nav__header__toggler hidden xl:block ml-auto text-white text-opacity-70 hover:text-opacity-100 transition-all duration-300 ease-in-out pr-5">
                        <i data-lucide="arrow-left-circle" class="w-5 h-5"></i>
                    </a>
                    <a href="javascript:;"
                        class="mobile-menu-toggler xl:hidden ml-auto text-white text-opacity-70 hover:text-opacity-100 transition-all duration-300 ease-in-out pr-5">
                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
            <div class="scrollable">
                <ul class="scrollable__content">
                    @if(session()->has('adminlogin'))
                    <li>
                        <a href="/admin/userlist" class="side-menu side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="corner-down-right"></i>
                            </div>
                            <div class="side-menu__title">Back to admin</div>
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="/dashboard" class="side-menu side-menu--active">
                            <div class="side-menu__icon">
                                <i data-lucide="home"></i>
                            </div>
                            <div class="side-menu__title">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="user-check"></i>
                            </div>
                            <div class="side-menu__title">
                                Profile
                                <div class="side-menu__sub-icon">
                                    <i data-lucide="chevron-down"></i>
                                </div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="{{ url('user/edit-profile') }}" class="side-menu">
                                    <div class="side-menu__icon">
                                        <i data-lucide="corner-down-right"></i>
                                    </div>
                                    <div class="side-menu__title">Edit Profile</div>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('user/reset-password') }}" class="side-menu">
                                    <div class="side-menu__icon">
                                        <i data-lucide="corner-down-right"></i>
                                    </div>
                                    <div class="side-menu__title">Reset Password</div>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('user/trans-password') }}" class="side-menu">
                                    <div class="side-menu__icon">
                                        <i data-lucide="corner-down-right"></i>
                                    </div>
                                    <div class="side-menu__title">Transaction Password</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!--<li>-->
                    <!--    <a href="/user/add-fund" class="side-menu">-->
                    <!--        <div class="side-menu__icon">-->
                    <!--            <i data-lucide="wallet"></i>-->
                    <!--        </div>-->
                    <!--        <div class="side-menu__title">Add Money</div>-->
                    <!--    </a>-->
                    <!--</li>-->
                    <li>
                        <a href="/user/add-fund-history" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="book"></i>
                            </div>
                            <div class="side-menu__title">Wallet Fund History</div>
                        </a>
                    </li>
                    <li>
                        <a href="/user/payin_report" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="book"></i>
                            </div>
                            <div class="side-menu__title">PayIn Report</div>
                        </a>
                    </li>
                    <li>
                        <a href="/user/payout_report" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="book"></i>
                            </div>
                            <div class="side-menu__title">PayOut Report</div>
                        </a>
                    </li>
                    <li>
                        <a href="/user/settlement_report" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="book"></i>
                            </div>
                            <div class="side-menu__title">Settlement Report</div>
                        </a>
                    </li>
                    <li>
                        <a href="/rudraxpay_docs.pdf" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="book"></i>
                            </div>
                            <div class="side-menu__title">Gateway Docs</div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="headphones"></i>
                            </div>
                            <div class="side-menu__title">
                                Support
                                <div class="side-menu__sub-icon">
                                    <i data-lucide="chevron-down"></i>
                                </div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="{{ url('user/support/chat-support') }}" class="side-menu">
                                    <div class="side-menu__icon">
                                        <i data-lucide="corner-down-right"></i>
                                    </div>
                                    <div class="side-menu__title">General Support</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ url('logout') }}" class="side-menu">
                            <div class="side-menu__icon">
                                <i data-lucide="log-out"></i>
                            </div>
                            <div class="side-menu__title">Logout</div>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="wrapper">
            <div class="top-bar">
                <nav aria-label="breadcrumb" class="-intro-x hidden xl:flex">
                    <ol class="breadcrumb breadcrumb-light">
                        <li class="breadcrumb-item active" aria-current="page"
                            style="font-size: 25px;
                        color: #ffffff !important;
                        font-weight: 900;">
                            Welcome to {{ strtoupper(setting('app_name')) }}
                        </li>
                    </ol>
                </nav>
                <div class="-intro-x xl:hidden mr-3 sm:mr-6">
                    <div class="mobile-menu-toggler cursor-pointer">
                        <i data-lucide="bar-chart-2"
                            class="mobile-menu-toggler__icon transform rotate-90 dark:text-slate-500"></i>
                    </div>
                </div>
                <div class="intro-x relative ml-auto sm:mx-auto">
                    <div class="search hidden sm:block"></div>
                </div>
                <div id="search-result-modal" class="modal flex items-center justify-center" tabindex="-1"
                    aria-hidden="true"></div>
                <div class="intro-x dropdown text-slate-200 h-10">
                    <div class="h-full dropdown-toggle flex items-center" role="button" aria-expanded="false"
                        data-tw-toggle="dropdown">
                        <div class="w-10 h-10 image-fit">
                            <img alt="user-image"
                                class="rounded-full border-2 border-white border-opacity-10 shadow-lg"
                                src="/assets/img/mothersolution.png" />
                        </div>
                        <div class="hidden md:block ml-3">
                            <div class="max-w-[7rem] truncate font-medium">
                                {{ user('name') }} </div>
                            <div class="text-xs text-slate-400"></div>
                        </div>
                    </div>
                    <div class="dropdown-menu w-56">
                        <ul class="dropdown-content">
                            <li>
                                <a href="{{ url('user/edit-profile') }}" class="dropdown-item">
                                    <i data-lucide="user" class="w-4 h-4 mr-2"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('user/reset-password') }}" class="dropdown-item">
                                    <i data-lucide="lock" class="w-4 h-4 mr-2"></i> Reset
                                    Password
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('user/support/chat-support') }}" class="dropdown-item">
                                    <i data-lucide="headphones" class="w-4 h-4 mr-2"></i>
                                    Support
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider" />
                            </li>
                            <li>
                                <a href="{{ url('logout') }}" class="dropdown-item">
                                    <i data-lucide="toggle-right" class="w-4 h-4 mr-2"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @yield('content')
        </div>
    </div>
    <script src="/assets3/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
        integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"
        integrity="sha512-rstIgDs0xPgmG6RX1Aba4KV5cWJbAMcvRCVmglpam9SoHZiUCyQVDdH2LPlxoHtrv17XWblE/V/PP+Tr04hbtA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="/admin_assets/js/sweet-alert.js"></script>
    <script src="/admin_assets/vendors/sweetalert2/sweetalert2.min.js"></script>
    <script src="/admin_assets/js/main.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.print.min.js"></script>
<script>
    let table = new DataTable('table',{
    layout: {
        topStart: {
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        }
    }
});
</script>
    @yield('js')
    <script>
        function myFunction(copyTexts) {
            var copyText = document.getElementById(copyTexts);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert('Copied Successfully!');
        }
    </script>
</body>

</html>
