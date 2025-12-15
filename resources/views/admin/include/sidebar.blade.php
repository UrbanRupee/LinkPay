<nav class="sidebar">
    <div class="sidebar-header">
        <a href="/admin/dashboard" class="sidebar-brand d-flex align-items-center justify-content-center">
            <img src="/assets-home/images/logo/logo.jpeg" alt="{{ setting('app_name') }} Logo" class="sidebar-logo">
        </a>
        {{-- Sidebar Toggler: Positioned absolutely on the right --}}
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            <li class="nav-item nav-category">Main</li>
            <li class="nav-item">
                <a href="/admin/dashboard" class="nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}"> {{-- Added active class logic --}}
                    <i class="link-icon" data-feather="home"></i>
                    <span class="link-title">Dashboard</span>
                </a>
            </li>
            {{-- Product Area (commented out in your original, kept as is) --}}
            {{-- ... --}}
            <li class="nav-item nav-category">Providers</li>
            <li class="nav-item">
                <a href="/admin/providers" class="nav-link {{ Request::is('admin/providers') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="book"></i> {{-- Changed icon to book for ledger --}}
                    <span class="link-title">All Providers</span>
                </a>
            </li>
            <li class="nav-item nav-category">Statement</li>
            <li class="nav-item">
                <a href="/admin/user_ledger" class="nav-link {{ Request::is('admin/user_ledger') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="book"></i> {{-- Changed icon to book for ledger --}}
                    <span class="link-title">Payin Ledger</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/payout_ledger" class="nav-link {{ Request::is('admin/payout_ledger') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="book"></i> {{-- Changed icon to book for ledger --}}
                    <span class="link-title">Payout Ledger</span>
                </a>
            </li>
            {{-- REMOVED: Card Ledger - Not needed
            <li class="nav-item">
                <a href="/admin/card_ledger" class="nav-link {{ Request::is('admin/card_ledger') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="credit-card"></i>
                    <span class="link-title">Card Ledger</span>
                </a>
            </li>
            --}}
            <li class="nav-item nav-category">Users area</li>
            @if(admin('role') == 'admin')
            <li class="nav-item">
                <a href="/admin/useradd" class="nav-link {{ Request::is('admin/useradd') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="user-plus"></i> {{-- Changed icon for add user --}}
                    <span class="link-title">Add User</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a href="/admin/userlist" class="nav-link {{ Request::is('admin/userlist') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="users"></i> {{-- Changed icon for user list --}}
                    <span class="link-title">User List</span>
                </a>
            </li>
            @if(admin('role') == 'admin')
            <li class="nav-item">
                <a href="/admin/verify_transfer" class="nav-link {{ Request::is('admin/verify_transfer') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="check-circle"></i> {{-- Icon for verification --}}
                    <span class="link-title">Transaction Verify</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/amount_transfer" class="nav-link {{ Request::is('admin/amount_transfer') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="repeat"></i> {{-- Icon for transfer --}}
                    <span class="link-title">Amount Transfer</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/2_club" class="nav-link {{ Request::is('admin/2_club') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="list"></i> {{-- Icon for history list --}}
                    <span class="link-title">Amount Transfer History</span>
                </a>
            </li>
            <li class="nav-item nav-category">Settlement area</li>
            <li class="nav-item">
                <a href="/admin/mannual_payment" class="nav-link {{ Request::is('admin/mannual_payment') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="send"></i> {{-- Icon for payout/send --}}
                    <span class="link-title">Manual Payout</span> {{-- Corrected typo from "Mannual" --}}
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/settlement" class="nav-link {{ Request::is('admin/settlement') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="credit-card"></i> {{-- Icon for settlement/card --}}
                    <span class="link-title">Settlement</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/settlement_list" class="nav-link {{ Request::is('admin/settlement_list') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="clipboard"></i> {{-- Icon for list/report --}}
                    <span class="link-title">All Settlement</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/hold_ledger" class="nav-link {{ Request::is('admin/hold_ledger') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="lock"></i> {{-- Icon for hold/lock --}}
                    <span class="link-title">Hold Ledger</span>
                </a>
            </li>
            <li class="nav-item nav-category">Request area</li>
            <li class="nav-item">
                <a href="/admin/user_request" class="nav-link {{ Request::is('admin/user_request') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="download"></i> {{-- Icon for incoming requests/recharges --}}
                    <span class="link-title">User Recharges</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/withdrawal_request" class="nav-link {{ Request::is('admin/withdrawal_request') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="upload"></i> {{-- Icon for outgoing requests/withdrawals --}}
                    <span class="link-title">Withdrawal Request</span>
                </a>
            </li>
            @endif {{-- End of admin role check --}}
            @if(admin('role') == 'admin')
            <li class="nav-item nav-category">Configurations</li>
            <li class="nav-item">
                <a href="/admin/payout_recon" class="nav-link {{ Request::is('admin/payout_recon') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="refresh-cw"></i> {{-- Icon for reconciliation --}}
                    <span class="link-title">Payout Recon</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/maintainPayinBalanace" class="nav-link {{ Request::is('maintainPayinBalanace') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="tool"></i> {{-- Icon for troubleshoot --}}
                    <span class="link-title">Payin wallet troubleshoot</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/restart_system" class="nav-link {{ Request::is('admin/restart_system') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="refresh-ccw"></i> {{-- Icon for restart --}}
                    <span class="link-title">Restart System</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/logs" class="nav-link {{ Request::is('admin/logs') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="file-text"></i> {{-- Icon for logs --}}
                    <span class="link-title">Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/setting" class="nav-link {{ Request::is('admin/setting') ? 'active' : '' }}">
                    <i class="link-icon" data-feather="settings"></i>
                    <span class="link-title">Setting</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
</nav>

{{-- This part is for the settings sidebar (NobleUI feature) --}}
<nav class="settings-sidebar">
    <div class="sidebar-body">
        <a href="#" class="settings-sidebar-toggler">
            <i data-feather="settings"></i>
        </a>
        <h6 class="text-muted mb-2">Sidebar:</h6>
        <div class="mb-3 pb-3 border-bottom">
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="sidebarThemeSettings" id="sidebarLight"
                    value="sidebar-light" checked>
                <label class="form-check-label" for="sidebarLight">
                    Light
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="sidebarThemeSettings" id="sidebarDark"
                    value="sidebar-dark">
                <label class="form-check-label" for="sidebarDark">
                    Dark
                </label>
            </div>
        </div>
    </div>
</nav>