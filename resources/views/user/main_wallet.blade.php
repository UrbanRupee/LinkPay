@extends('user.layout.NewUser')

@section('css')
    <style>
        /* Orange Theme - Wallet Page */
        .card-header {
            background-color: #FFEDD5;
            border-bottom: 2px solid #F15A22;
            padding: 1.25rem 1.5rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 700;
            color: #F15A22;
        }

        /* Wallet Balance Cards */
        .wallet-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem; /* Slightly reduced padding */
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
            border: 1px solid rgba(0, 0, 0, 0.05);
            min-height: 100px; /* Adjusted consistent card height */
        }
        .wallet-card:hover {
            transform: translateY(-3px);
        }
        .wallet-card .icon-wrapper {
            background-color: rgba(241, 90, 34, 0.1);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 1.25rem;
            border: 2px solid rgba(241, 90, 34, 0.3);
        }
        .wallet-card .icon-wrapper i {
            color: #F15A22;
            font-size: 1.8rem;
        }
        .wallet-card .balance-info {
            flex-grow: 1;
        }
        .wallet-card .balance-label {
            font-size: 1rem;
            color: #1F2937;
            margin-bottom: 0.15rem; /* Reduced margin */
        }
        .wallet-card .balance-amount {
            font-size: 1.75rem; /* Significantly reduced balance amount font size */
            font-weight: 700;
            color: var(--primary-text-color);
            line-height: 1.2; /* Adjusted line height */
        }

        /* Specific card colors/icons (retained from previous) */
        .wallet-card.main-wallet .icon-wrapper { background-color: rgba(241, 90, 34, 0.15); }
        .wallet-card.main-wallet .icon-wrapper i { color: #F15A22; }
        .wallet-card.payin-wallet .icon-wrapper { background-color: rgba(241, 90, 34, 0.1); }
        .wallet-card.payin-wallet .icon-wrapper i { color: #F15A22; }
        .wallet-card.payout-wallet .icon-wrapper { background-color: rgba(245, 158, 11, 0.1); }
        .wallet-card.payout-wallet .icon-wrapper i { color: #F59E0B; }
        .wallet-card.aeps-wallet .icon-wrapper { background-color: rgba(241, 90, 34, 0.1); }
        .wallet-card.aeps-wallet .icon-wrapper i { color: #D14A15; }
        .wallet-card.hold-wallet .icon-wrapper { background-color: rgba(107, 114, 128, 0.1); }
        .wallet-card.hold-wallet .icon-wrapper i { color: #6B7280; }

    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h2 class="h4 font-weight-bold mb-4">{{ $title }} Overview</h2>
            </div>
        </div>

        {{-- Wallet Balance Cards --}}
        <div class="col-lg-4 col-md-6">
            <div class="wallet-card payin-wallet">
                <div class="icon-wrapper">
                    <i data-lucide="chevrons-down"></i> {{-- Icon for funds coming IN --}}
                </div>
                <div class="balance-info">
                    <div class="balance-label">PayIn Wallet</div>
                    <div class="balance-amount">{{ balance(wallet(user('userid'),'int','payin')) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="wallet-card payout-wallet">
                <div class="icon-wrapper">
                    <i data-lucide="chevrons-up"></i> {{-- Icon for funds going OUT --}}
                </div>
                <div class="balance-info">
                    <div class="balance-label">PayOut Wallet</div>
                    <div class="balance-amount">{{ balance(wallet(user('userid'),'int','payout')) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="wallet-card aeps-wallet">
                <div class="icon-wrapper">
                    <i data-lucide="banknote"></i> {{-- General money/bank icon --}}
                </div>
                <div class="balance-info">
                    <div class="balance-label">AEPS Wallet</div>
                    <div class="balance-amount">{{ balance(wallet(user('userid'),'int','wallet')) }}</div> {{-- Assuming 'wallet' key for AEPS --}}
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="wallet-card hold-wallet"> {{-- Added a class for Hold Wallet --}}
                <div class="icon-wrapper">
                    <i data-lucide="lock"></i> {{-- Lock icon for hold --}}
                </div>
                <div class="balance-info">
                    <div class="balance-label">Hold Wallet</div>
                    <div class="balance-amount">{{ balance(wallet(user('userid'),'int','hold')) }}</div>
                </div>
            </div>
        </div>
        {{-- You can add more wallet cards here as needed --}}
    </div> {{-- End row for wallet cards --}}

    {{-- The P2P transfer section has been removed --}}

</div>
@endsection

@section('js')
    {{-- All P2P related JavaScript has been removed --}}
    <script>
        $(document).ready(function() {
            // No specific JS for this page as it's purely for display.
            // If you later add any client-side features, put them here.
        });
    </script>
@endsection