<div class="axil-mainmenu">
    <div class="container">
        <div class="header-navbar">
            <div class="header-brand">
                <a href="/" class="logo logo-dark">
                    <img src="/assets/images/logo/logo.png" alt="Site Logo" style="width: 167px;">
                </a>
                <a href="/" class="logo logo-light">
                    <img src="/assets/images/logo/logo.png" alt="Site Logo" style="width: 167px;">
                </a>
            </div>

            <div class="header-main-nav">
                <!-- Start Mainmanu Nav -->
                <nav class="mainmenu-nav">
                    <button class="mobile-close-btn mobile-nav-toggler"><i class="fas fa-times"></i></button>
                    <div class="mobile-nav-brand">
                        <a href="/" class="logo">
                            <img src="/assets/images/logo/logo.png" alt="Site Logo">
                        </a>
                    </div>
                    <ul class="mainmenu">
                        <li>
                            <a href="/">Home</a>
                        </li>
                        <li>
                            <a href="/shop">Shop</a>
                        </li>
                        <li><a href="/about-us">About</a></li>
                        {{-- <li class="menu-item-has-children"> --}}
                        <li>
                            <a href="/blogs">Blog</a>
                        </li>
                        <li><a href="/contact">Contact</a></li>
                    </ul>
                </nav>
                <!-- End Mainmanu Nav -->
            </div>
            <div class="col-xl-2">
                <div class="social-share">
                    <a href="{{setting('facebook')}}"><i class="fab fa-facebook-f" style="color:blue;"></i></a>
                    <a href="{{setting('instagram')}}"><i class="fab fa-instagram" style="color:violet;"></i></a>
                    <a href="{{setting('twitter')}}"><i class="fab fa-twitter" style="color:rgb(51, 150, 230);"></i></a>
                    <a href="{{setting('youtube')}}"><i class="fab fa-youtube" style="color:red;"></i></a>
                </div>
            </div>
            <div class="header-action">
                <ul class="action-list">
                    {{-- <li class="axil-search">
                        <a href="javascript:void(0)" class="header-search-icon" title="Search">
                            <i class="flaticon-magnifying-glass"></i>
                        </a>
                    </li> --}}
                    @if (session()->has('userlogin'))
                    <li class="wishlist">
                        <a href="/wishlist">
                            <i class="flaticon-heart"></i>
                        </a>
                    </li>
                    <li class="shopping-cart">
                        <a href="#" class="cart-dropdown-btn">
                            <span class="cart-count">{{count(usercart(user('userid')))}}</span>
                            <i class="flaticon-shopping-cart"></i>
                        </a>
                    </li>
                    @endif
                    <li class="my-account">
                        <a href="javascript:void(0)">
                            <i class="flaticon-person"></i>
                        </a>
                        <div class="my-account-dropdown">
                            <span class="title">Account</span>
                            @if (session()->has('userlogin') || session()->has('adminlogin'))
                            <p class="text-primary">
                                Hello! {{ session()->has('userlogin') ? session()->get('userlogin')->name : session()->get('adminlogin')->name }}
                            </p>
                                <ul>
                                    <li>
                                        <a href="/dashboard">My Dashboard</a>
                                    </li>
                                    {{-- <li>
                                        <a href="#">My Orders</a>
                                    </li> --}}
                                </ul>
                                <a href="/logout_all" class="axil-btn btn-bg-primary">Logout</a>
                            @else
                                <div class="login-btn">
                                    <a href="/login" class="axil-btn btn-bg-primary">Login</a>
                                </div>
                                <div class="reg-footer text-center">No account yet? <a href="/register"
                                        class="btn-link">REGISTER HERE.</a></div>
                            @endif
                        </div>
                    </li>
                    <li class="axil-mobile-toggle">
                        <button class="menu-btn mobile-nav-toggler">
                            <i class="flaticon-menu-2"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>