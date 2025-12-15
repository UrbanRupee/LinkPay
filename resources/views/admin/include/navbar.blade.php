<nav class="navbar">
    <a href="#" class="sidebar-toggler">
        <i data-feather="menu"></i>
    </a>
    <div class="navbar-content">
        <form class="search-form">
           
        </form>
        <ul class="navbar-nav">
            @if(admin('role') == 'franchise')
            <li class="nav-item dropdown">
                {{-- Changed to a span or div with proper styling so it adheres to the new button styles --}}
                <span class="btn btn-warning" style="width: max-content; pointer-events: none;">
                    Wallet = {{ balance(wallet(admin('userid'),'str','wallet')) }}
                </span>
            </li>
            @endif
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{-- Use the common image path and better classes --}}
                    <img class="wd-30 ht-30 rounded-circle profile-pic" src="{{ asset('assets/img/mothersolution.png') }}" alt="User Profile">
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">
                            {{-- Use the common image path and better classes --}}
                            <img class="wd-80 ht-80 rounded-circle profile-pic-lg" src="{{ asset('assets/img/mothersolution.png') }}"
                                alt="User Profile">
                        </div>
                        <div class="text-center">
                            <p class="tx-16 fw-bolder">{{admin('name')}}</p>
                            <p class="tx-12 text-muted">{{admin('email')}}</p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        <li class="dropdown-item py-2">
                            <a href="{{url('admin/change-password')}}" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="settings"></i> {{-- Changed icon to settings --}}
                                <span>Change Password</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2">
                            <a href="/admin/logout" class="text-body ms-0">
                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                <span>Log Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>