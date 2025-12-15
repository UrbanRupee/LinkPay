@extends('user.layout.NewUser')

@section('css')
    <style>
        /* RED & BLACK THEME - PayIn Report Clean Design */
        .payin-report-container {
            background: var(--black);
            padding: 1.5rem;
            min-height: 100vh;
        }

        /* Page Header */
        .report-header {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-left: 4px solid var(--primary-orange);
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .report-header h5 {
            color: #FFFFFF;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }

        /* Status Summary Cards */
        .status-summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            border-left: 4px solid var(--primary-orange);
            transition: transform 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(241, 90, 34, 0.3);
        }

        .summary-card.success { border-left-color: var(--primary-orange); }
        .summary-card.pending { border-left-color: var(--light-orange); }
        .summary-card.failed { border-left-color: var(--dark-red); }

        .summary-card .count {
            font-size: 2.5rem;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 0.35rem;
        }

        .summary-card .amount {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-orange);
            margin-bottom: 0.25rem;
        }

        .summary-card .label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1F2937;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-card h6.label {
            color: #1F2937 !important;
        }

        /* Filter Section */
        .filter-panel {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }

        .filter-title {
            color: #FFFFFF;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .filter-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            border: 2px solid var(--primary-orange);
            background: var(--black);
            color: #FFFFFF;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .filter-btn:hover {
            background: var(--dark-gray);
            color: #FFFFFF;
            text-decoration: none;
            transform: translateY(-2px);
            border-color: var(--light-orange);
        }

        .filter-btn.active {
            background: var(--black);
            color: var(--primary-orange);
            border-color: var(--primary-orange);
            border-width: 3px;
            font-weight: 700;
        }

        .filter-form .row {
            margin-bottom: 0;
        }

        .filter-form .form-label {
            color: #FFFFFF;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .filter-form .form-control {
            background-color: var(--medium-gray);
            border: 1px solid var(--primary-orange);
            color: #FFFFFF;
            border-radius: 8px;
            padding: 0.6rem 1rem;
        }

        .filter-form .form-control:focus {
            background-color: var(--dark-gray);
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25);
            color: #FFFFFF;
        }

        .filter-form .form-control::placeholder {
            color: #9CA3AF;
        }

        .filter-form .btn-primary {
            background-color: var(--primary-orange);
            border-color: var(--primary-orange);
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
        }

        .filter-form .btn-primary:hover {
            background-color: var(--dark-red);
            border-color: var(--dark-red);
        }

        .filter-form .btn-secondary {
            background-color: var(--medium-gray);
            border-color: var(--medium-gray);
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
        }

        .filter-form .btn-secondary:hover {
            background-color: var(--dark-gray);
            border-color: var(--primary-orange);
        }

        /* Table Section */
        .table-panel {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            overflow: hidden;
        }

        .export-btn {
            background: transparent;
            border: 1px solid var(--primary-orange);
            color: #fff;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .export-btn:hover {
            background: var(--primary-orange);
            color: #111827;
            text-decoration: none;
        }

        .table-panel-actions {
            padding: 1.25rem 1.5rem;
            text-align: end;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .table-panel-body {
            padding: 0;
        }
        .ledger-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .ledger-table thead th {
            background: #F9FAFB;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4B5563;
            padding: 1rem;
            border-bottom: 1px solid #E5E7EB;
        }
        .ledger-table tbody td {
            padding: 0.75rem;
            border-bottom: 1px solid #F3F4F6;
            font-size: 0.9rem;
            vertical-align: middle;
            transition: all 0.2s ease;
        }
        .ledger-table tbody td.amount-cell {
            color: #1F2937;
            font-weight: 600;
        }
        .ledger-table tbody td.fees-cell {
            color: #DC2626;
        }
        .ledger-table tbody td.settled-cell {
            color: #047857;
            font-weight: 600;
        }
        .badge-mode {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #FEF3C7;
            color: #B45309;
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-mode.upi {
            background: #DBEAFE;
            color: #1D4ED8;
        }
        .badge-mode.netbanking {
            background: #E0E7FF;
            color: #4338CA;
        }
        .badge-mode.card {
            background: #FCE7F3;
            color: #BE185D;
        }
        .badge-mode.easebuzz {
            background: #FFE4E6;
            color: #DB2777;
        }
        .card-category-pill {
            display: inline-block;
            background: #F3F4F6;
            color: #4B5563;
            border-radius: 6px;
            padding: 0.15rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .payment-detail-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 999px;
            padding: 0.25rem 0.6rem;
            font-size: 0.75rem;
            color: #374151;
            margin: 0.1rem 0.3rem 0.1rem 0;
        }
        .status-badge-success {
            background: #DCFCE7;
            color: #166534;
            border-radius: 999px;
            padding: 0.3rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-badge-pending {
            background: #FEF3C7;
            color: #B45309;
            border-radius: 999px;
            padding: 0.3rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-badge-failed {
            background: #FEE2E2;
            color: #B91C1C;
            border-radius: 999px;
            padding: 0.3rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .gateway-view-btn {
            border: 1px solid #D1D5DB;
            background: #F3F4F6;
            color: #1F2937;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
        }
        .gateway-view-btn:hover {
            background: #E5E7EB;
        }
        .print-order-btn {
            border: 1px solid #C7D2FE;
            background: #EEF2FF;
            color: #4338CA;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            text-decoration: none;
        }
        .print-order-btn:hover {
            background: #E0E7FF;
            color: #3730A3;
        }
        .ledger-table tbody tr:hover {
            background: #F9FAFB;
        }
        .ledger-meta-list {
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 0.9rem;
            color: #4B5563;
        }
        .ledger-meta-list li + li {
            margin-top: 0.25rem;
        }
        .badge-mode {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #EEF2FF;
            color: #4338CA;
            border-radius: 999px;
            padding: 0.2rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .amount-cell {
            font-weight: 600;
            color: #111827;
        }
        .text-muted {
            color: #9CA3AF !important;
        }

        /* DataTable Overrides */
        .dataTables_wrapper {
            color: #FFFFFF;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            color: #FFFFFF;
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: #FFFFFF;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background-color: var(--medium-gray);
            border: 1px solid var(--primary-orange);
            color: #FFFFFF;
            border-radius: 6px;
            padding: 0.4rem 0.6rem;
            margin: 0 0.5rem;
        }

        .dataTables_wrapper .dataTables_info {
            color: #FFFFFF;
        }

        .dataTables_wrapper .dataTables_paginate {
            color: #FFFFFF;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #FFFFFF !important;
            background: var(--medium-gray);
            border: 1px solid var(--primary-orange);
            border-radius: 6px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--primary-orange) !important;
            color: #FFFFFF !important;
            border-color: var(--primary-orange);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-orange) !important;
            color: #FFFFFF !important;
            border-color: var(--primary-orange);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #666 !important;
            background: var(--black);
        }

        /* Table Styling */
        .table {
            width: 100%;
            margin-bottom: 0;
            color: #FFFFFF;
        }
        
        .table thead th {
            background-color: var(--black);
            color: var(--primary-orange);
            font-weight: 600;
            padding: 1rem 0.75rem;
            border: none;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--medium-gray);
            color: #FFFFFF;
            background: transparent;
        }

        .table tbody tr {
            background: transparent;
        }

        .table tbody tr:nth-child(even) {
            background: rgba(241, 90, 34, 0.03);
        }

        .table tbody tr:hover {
            background: rgba(241, 90, 34, 0.1);
        }

        /* Status Badges */
        .badge-success {
            background: var(--primary-orange);
            color: #FFFFFF;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-warning {
            background: var(--light-orange);
            color: var(--black);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-danger {
            background: var(--dark-red);
            color: #FFFFFF;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #9CA3AF;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-orange);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h5 {
            color: #FFFFFF;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #9CA3AF;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .payin-report-container {
                padding: 1rem;
            }
            
            .summary-card {
                margin-bottom: 1rem;
            }
            
            .filter-buttons {
                flex-direction: column;
            }
            
            .filter-btn {
                width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="payin-report-container">
    @php
        $statusSummary = $statusSummary ?? [
            'success' => ['count' => 0, 'amount' => 0],
            'pending' => ['count' => 0, 'amount' => 0],
            'failed' => ['count' => 0, 'amount' => 0],
        ];

        $statusUrl = function (?string $status) {
            $query = request()->except('status_filter', 'page');
            if (!is_null($status)) {
                $query['status_filter'] = $status;
            }
            $query = array_filter($query, fn($value) => $value !== null && $value !== '');
            $queryString = http_build_query($query);
            return url()->current() . ($queryString ? '?' . $queryString : '');
        };

        $exportQuery = array_filter([
            'status_filter' => request('status_filter'),
            'period' => request('period'),
            'from_date' => request('from_date'),
            'to_date' => request('to_date'),
            'search' => request('search', request('search_query')),
        ], fn($value) => $value !== null && $value !== '');

        $exportUrl = route('user.export.payin_report') . (count($exportQuery) ? '?' . http_build_query($exportQuery) : '');
    @endphp
                
                    <!-- Status Summary Cards -->
    <div class="status-summary-row">
        <div class="summary-card success">
            <div class="count">{{ number_format($statusSummary['success']['count']) }}</div>
            <div class="amount">₹{{ number_format($statusSummary['success']['amount'], 2) }}</div>
            <div class="label">Successful Transactions</div>
                                </div>
        <div class="summary-card pending">
            <div class="count">{{ number_format($statusSummary['pending']['count']) }}</div>
            <div class="amount">₹{{ number_format($statusSummary['pending']['amount'], 2) }}</div>
            <div class="label">Pending Transactions</div>
                            </div>
        <div class="summary-card failed">
            <div class="count">{{ number_format($statusSummary['failed']['count']) }}</div>
            <div class="amount">₹{{ number_format($statusSummary['failed']['amount'], 2) }}</div>
            <div class="label">Failed Transactions</div>
                        </div>
                    </div>

    <!-- Analytics Cards -->
    @php
        $analytics = $analytics ?? [];
        $successRate = $analytics['successRate'] ?? 0;
        $avgTransactionAmount = $analytics['avgTransactionAmount'] ?? 0;
        $totalTransactions = $analytics['totalTransactions'] ?? 0;
        $successTransactions = $analytics['successTransactions'] ?? 0;
        
        // Debug: Uncomment to see analytics data
        // dd($analytics);
    @endphp
    <div class="status-summary-row" style="margin-top: 1.5rem;">
        <div class="summary-card" style="border-left-color: #10B981; background: #F0FDF4;">
            <div class="count" style="color: #1F2937;">{{ number_format($successRate, 1) }}%</div>
            <div class="amount" style="color: #10B981;">Success Rate</div>
            <div class="label" style="color: #1F2937;">Transaction Success Rate</div>
        </div>
        <div class="summary-card" style="border-left-color: #3B82F6; background: #EFF6FF;">
            <div class="count" style="color: #1F2937;">₹{{ number_format($avgTransactionAmount, 2) }}</div>
            <div class="amount" style="color: #3B82F6;">Average Amount</div>
            <div class="label" style="color: #1F2937;">Per Transaction</div>
        </div>
        <div class="summary-card" style="border-left-color: #8B5CF6; background: #F5F3FF;">
            <div class="count" style="color: #1F2937;">{{ number_format($totalTransactions) }}</div>
            <div class="amount" style="color: #8B5CF6;">Total Transactions</div>
            <div class="label" style="color: #1F2937;">All Status Combined</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
        <div class="col-md-6" style="margin-bottom: 1.5rem;">
            <div class="summary-card" style="min-height: 350px; background: #FFFFFF;">
                <h6 class="label" style="margin-bottom: 1rem; font-size: 1rem; color: #1F2937;">
                    <i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>Daily Transaction Trends
                </h6>
                <canvas id="dailyTrendChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
        <div class="col-md-6" style="margin-bottom: 1.5rem;">
            <div class="summary-card" style="min-height: 350px; background: #FFFFFF;">
                <h6 class="label" style="margin-bottom: 1rem; font-size: 1rem; color: #1F2937;">
                    <i class="fas fa-chart-pie" style="margin-right: 0.5rem;"></i>Payment Mode Distribution
                </h6>
                <canvas id="modeDistributionChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
        <div class="col-md-6" style="margin-bottom: 1.5rem;">
            <div class="summary-card" style="min-height: 350px; background: #FFFFFF;">
                <h6 class="label" style="margin-bottom: 1rem; font-size: 1rem; color: #1F2937;">
                    <i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i>Hourly Transaction Volume
                </h6>
                <canvas id="hourlyChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
        <div class="col-md-6" style="margin-bottom: 1.5rem;">
            <div class="summary-card" style="min-height: 350px; background: #FFFFFF;">
                <h6 class="label" style="margin-bottom: 1rem; font-size: 1rem; color: #1F2937;">
                    <i class="fas fa-chart-area" style="margin-right: 0.5rem;"></i>Status Distribution
                </h6>
                <canvas id="statusChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Period-Based Charts -->
    <div class="row" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
        <div class="col-md-12" style="margin-bottom: 1.5rem;">
            <div class="summary-card" style="min-height: 400px; background: #FFFFFF;">
                <h6 class="label" style="margin-bottom: 1rem; font-size: 1rem; color: #1F2937;">
                    <i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>Period-Based Transaction Analysis
                </h6>
                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary period-btn active" data-period="today">Today</button>
                    <button type="button" class="btn btn-sm btn-outline-primary period-btn" data-period="yesterday">Yesterday</button>
                    <button type="button" class="btn btn-sm btn-outline-primary period-btn" data-period="weekly">Weekly</button>
                    <button type="button" class="btn btn-sm btn-outline-primary period-btn" data-period="monthly">Monthly</button>
                </div>
                <canvas id="periodChart" style="max-height: 350px;"></canvas>
            </div>
                        </div>
                    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
        <h6 class="filter-title"><i class="fas fa-filter" style="margin-right: 0.5rem;"></i>Filter by Status</h6>
        
        <div class="filter-buttons">
            <a href="{{ $statusUrl(null) }}" class="filter-btn {{ !request('status_filter') ? 'active' : '' }}">
                <i class="fas fa-list" style="margin-right: 0.5rem;"></i>All Transactions
            </a>
            <a href="{{ $statusUrl('success') }}" class="filter-btn {{ request('status_filter') == 'success' ? 'active' : '' }}">
                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>Success Only
            </a>
            <a href="{{ $statusUrl('pending') }}" class="filter-btn {{ request('status_filter') == 'pending' ? 'active' : '' }}">
                <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>Pending Only
            </a>
            <a href="{{ $statusUrl('failed') }}" class="filter-btn {{ request('status_filter') == 'failed' ? 'active' : '' }}">
                <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>Failed Only
                        </a>
                    </div>

        <hr style="border-color: var(--medium-gray); margin: 1.5rem 0;">

        <h6 class="filter-title"><i class="fas fa-calendar-day" style="margin-right: 0.5rem;"></i>Quick Date Filters</h6>
        <div class="filter-buttons">
            <a href="{{ request()->fullUrlWithQuery(['period' => 'today']) }}" class="filter-btn {{ request('period', $period ?? 'all') === 'today' ? 'active' : '' }}">
                <i class="fas fa-sun" style="margin-right: 0.5rem;"></i>Today
            </a>
            <a href="{{ request()->fullUrlWithQuery(['period' => 'yesterday']) }}" class="filter-btn {{ request('period') === 'yesterday' ? 'active' : '' }}">
                <i class="fas fa-history" style="margin-right: 0.5rem;"></i>Yesterday
            </a>
            <a href="{{ request()->fullUrlWithQuery(['period' => 'last7']) }}" class="filter-btn {{ request('period') === 'last7' ? 'active' : '' }}">
                <i class="fas fa-calendar-week" style="margin-right: 0.5rem;"></i>Last 7 Days
            </a>
            <a href="{{ request()->fullUrlWithQuery(['period' => 'all']) }}" class="filter-btn {{ request('period', 'all') === 'all' ? 'active' : '' }}">
                <i class="fas fa-infinity" style="margin-right: 0.5rem;"></i>All Time
                        </a>
                    </div>

        <hr style="border-color: var(--medium-gray); margin: 1.5rem 0;">

        <h6 class="filter-title"><i class="fas fa-calendar-alt" style="margin-right: 0.5rem;"></i>Filter by Date & Search</h6>
        
                        <form action="{{ url()->current() }}" method="GET" class="filter-form">
                            @if(request('status_filter'))
                                <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
                            @endif
                            @if(request('period'))
                                <input type="hidden" name="period" value="{{ request('period') }}">
                            @endif
            
            <div class="row g-3">
                            <div class="col-md-4">
                    <label for="from_date" class="form-label">From Date:</label>
                    <input type="text" class="form-control" id="from_date" name="from_date" 
                           value="{{ request('from_date') }}" placeholder="Select From Date">
                            </div>
                            <div class="col-md-4">
                    <label for="to_date" class="form-label">To Date:</label>
                    <input type="text" class="form-control" id="to_date" name="to_date" 
                           value="{{ request('to_date') }}" placeholder="Select To Date">
                            </div>
                                <div class="col-md-4">
                    <label for="search_query" class="form-label">Search:</label>
                    <input type="text" class="form-control" id="search_query" name="search" 
                           value="{{ request('search', request('search_query')) }}" placeholder="Transaction ID or UTR No.">
                                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-3 gap-2" style="gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search" style="margin-right: 0.5rem;"></i>Apply Filters
                                    </button>
                                    <a href="{{ url()->current() }}" class="btn btn-secondary">
                    <i class="fas fa-redo" style="margin-right: 0.5rem;"></i>Reset
                                    </a>
                            </div>
                        </form>
                            </div>

    <!-- Table Panel -->
    <div class="table-panel">
        <div class="table-panel-actions">
            <a href="{{ $exportUrl }}" class="export-btn">
                <i class="fas fa-file-export" style="margin-right: 0.35rem;"></i>Export CSV
            </a>
                            </div>
        
        <div class="table-panel-body">
                    <div class="table-responsive">
                <table class="table ledger-table" id="payinReportTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                            <th>Reference</th>
                            <th>Gateway Txn</th>
                                    <th>Amount</th>
                            <th>Fees</th>
                            <th>Settled</th>
                            <th>Mode</th>
                            <th>Card Category</th>
                            <th>Payment Details</th>
                                    <th>Status</th>
                            <th>UTR</th>
                                        <th>Date & Time</th>
                            <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $item)
                            @php
                                $meta = $item->gateway_meta ?? [];
                                $reference = $meta['reference'] ?? $item->transaction_id;
                                $gatewayTxn = $meta['gateway_txn'] ?? ($meta['reference'] ?? $item->transaction_id);
                                $fees = $meta['fees'] ?? (float) ($item->tax ?? 0);
                                $settled = $meta['settled'] ?? max(($item->amount ?? 0) - ($item->tax ?? 0), 0);
                                $paymentDetails = $meta['payment_details'] ?? [];
                                $payloadJson = null;
                                $displayUtr = $meta['utr'] ?? ($item->data4 ?? $item->data1);
                                if (!empty($item->callback_payload)) {
                                    $payloadJson = is_string($item->callback_payload)
                                        ? $item->callback_payload
                                        : json_encode($item->callback_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                }
                            @endphp
                                    <tr>
                                <td class="text-center">{{ ($list->currentPage() - 1) * $list->perPage() + $loop->iteration }}</td>
                                <td><strong>{{ $reference }}</strong></td>
                                <td>{{ $gatewayTxn ?? '—' }}</td>
                                <td class="text-right amount-cell">₹{{ number_format($item->amount, 2) }}</td>
                                <td class="text-right fees-cell">₹{{ number_format($fees, 2) }}</td>
                                <td class="text-right settled-cell">₹{{ number_format($settled, 2) }}</td>
                                <td>
                                    @if(!empty($meta['mode']) && $meta['mode'] !== '—')
                                        <span class="badge-mode {{ \Illuminate\Support\Str::slug(\Illuminate\Support\Str::lower($meta['mode'])) }}">
                                            <i class="fas fa-credit-card"></i>{{ $meta['mode'] }}
                                                    </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ !empty($meta['card_category']) && $meta['card_category'] !== '—' ? $meta['card_category'] : '—' }}</td>
                                <td>
                                    @if(!empty($paymentDetails))
                                        <div>
                                            @foreach($paymentDetails as $line)
                                                <span class="payment-detail-tag"><i class="fas fa-info-circle"></i>{{ $line }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                            <td>
                                                @if($item->status == 1)
                                        <span class="status-badge-success"><i class="fas fa-check-circle" style="margin-right: 0.3rem;"></i>Success</span>
                                                @elseif($item->status == 0)
                                        <span class="status-badge-pending"><i class="fas fa-clock" style="margin-right: 0.3rem;"></i>Pending</span>
                                                @else
                                        <span class="status-badge-failed"><i class="fas fa-times-circle" style="margin-right: 0.3rem;"></i>Failed</span>
                                                @endif
                                            </td>
                                <td>{{ $displayUtr ?: '—' }}</td>
                                <td data-order="{{ optional($item->created_at)->timestamp ?? 0 }}">{{ dformat($item->created_at, 'd-m-Y H:i:s') }}</td>
                                <td class="text-center">
                                    <a href="#" class="print-order-btn" onclick="printTransaction('{{ $item->transaction_id }}'); return false;" title="Print Order">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                                    </tr>
                                @empty
                                    <tr class="empty-state-row">
                                <td colspan="13">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox"></i>
                                        <h5>No Transactions Found</h5>
                                                    <p>Try adjusting your filters or check back later.</p>
                                                </div>
                                            </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

</div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Analytics Data
        const analyticsData = @json($analytics ?? []);
        
        // Chart Colors
        const chartColors = {
            primary: '#F15A22',
            success: '#10B981',
            warning: '#F59E0B',
            danger: '#EF4444',
            info: '#3B82F6',
            purple: '#8B5CF6',
            orange: '#F97316',
        };
        $(document).ready(function() {
            // Initialize Flatpickr for date inputs
            flatpickr("#from_date", {
                dateFormat: "Y-m-d"
            });
            
            flatpickr("#to_date", {
                dateFormat: "Y-m-d"
            });

            // Initialize DataTable (defensive init to prevent re-init errors)
            if ($('#payinReportTable').length) {
                // Remove empty state rows before initializing DataTables
                $('#payinReportTable tbody tr:has(td[colspan])').remove();
                
                // Check if table has actual data rows
                var hasData = $('#payinReportTable tbody tr').length > 0;
                
                if (hasData) {
                // Destroy existing if present
                if ($.fn.DataTable.isDataTable('#payinReportTable')) {
                    $('#payinReportTable').DataTable().destroy();
                }
                
                    // Initialize fresh - Simplified without Buttons extension
                    var table = $('#payinReportTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "ordering": true,
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        "pageLength": 10,
                        "order": [[11, "desc"]], // Sort by Date & Time descending
                    "language": {
                        "search": "Search transactions:",
                        "lengthMenu": "Show _MENU_ transactions per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ transactions",
                        "infoEmpty": "Showing 0 to 0 of 0 transactions",
                        "emptyTable": "No transactions found",
                        "zeroRecords": "No matching transactions found",
                        "paginate": {
                            "first": "«",
                            "last": "»",
                            "next": "›",
                            "previous": "‹"
                        }
                    },
                    "columnDefs": [
                            { "orderable": false, "targets": [0, 12] },
                            { "className": "text-center", "targets": [0, 6, 7, 9, 10, 11, 12] },
                            { "className": "text-right", "targets": [3, 4, 5] }
                        ],
                        "autoWidth": false,
                        "retrieve": true
                    });

                    // Update row numbers on order/search/draw
                    table.on('order.dt search.dt draw.dt', function () {
                        let start = table.page.info().start;
                        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                            cell.innerHTML = start + i + 1;
                        });
                    }).draw();
                }
            }
            
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        window.printTransaction = function(txnId) {
            if (!txnId) return;
            const url = `{{ url('/payment-success') }}?txn=${txnId}`;
            const printWindow = window.open(url, '_blank');
            if (!printWindow) {
                alert('Please allow popups to print the order.');
            }
        };

        // Initialize Charts after page loads and Chart.js is ready
        if (typeof Chart !== 'undefined') {
            initializeCharts();
        } else {
            // Wait for Chart.js to load
            window.addEventListener('load', function() {
                setTimeout(initializeCharts, 100);
            });
        }

        function initializeCharts() {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }
            
            console.log('Analytics Data:', analyticsData);
            
            // Daily Trends Chart
            const dailyTrendsCtx = document.getElementById('dailyTrendChart');
            if (dailyTrendsCtx) {
                const dailyData = analyticsData.dailyTrends || [];
                
                if (dailyData.length === 0) {
                    dailyTrendsCtx.parentElement.innerHTML = '<div style="color: #FFFFFF; text-align: center; padding: 2rem;">No data available for the selected period</div>';
                    return;
                }
                new Chart(dailyTrendsCtx, {
                    type: 'line',
                    data: {
                        labels: dailyData.map(d => d.date),
                        datasets: [{
                            label: 'Transaction Amount (₹)',
                            data: dailyData.map(d => d.amount),
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            tension: 0.4,
                            fill: true,
                        }, {
                            label: 'Transaction Count',
                            data: dailyData.map(d => d.count),
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '20',
                            tension: 0.4,
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#1F2937' }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            }
                        }
                    }
                });
            }

            // Payment Mode Distribution Chart
            const modeDistCtx = document.getElementById('modeDistributionChart');
            if (modeDistCtx) {
                const modeData = analyticsData.modeDistribution || {};
                const modes = Object.keys(modeData);
                
                if (modes.length === 0) {
                    modeDistCtx.parentElement.innerHTML = '<div style="color: #FFFFFF; text-align: center; padding: 2rem;">No payment mode data available</div>';
                    return;
                }
                
                const colors = [chartColors.primary, chartColors.info, chartColors.success, chartColors.warning, chartColors.purple, chartColors.orange];
                
                new Chart(modeDistCtx, {
                    type: 'doughnut',
                    data: {
                        labels: modes,
                        datasets: [{
                            data: modes.map(m => modeData[m].amount || 0),
                            backgroundColor: colors.slice(0, modes.length),
                            borderColor: '#1F2937',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#1F2937', padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const mode = context.label;
                                        const amount = context.parsed;
                                        const count = modeData[mode]?.count || 0;
                                        return `${mode}: ₹${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})} (${count} transactions)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Hourly Transaction Chart
            const hourlyCtx = document.getElementById('hourlyChart');
            if (hourlyCtx) {
                const hourlyData = analyticsData.hourlyData || [];
                
                if (!hourlyData || hourlyData.length === 0) {
                    hourlyCtx.parentElement.innerHTML = '<div style="color: #FFFFFF; text-align: center; padding: 2rem;">No hourly data available</div>';
                    return;
                }
                new Chart(hourlyCtx, {
                    type: 'bar',
                    data: {
                        labels: hourlyData.map(h => h.hour + ':00'),
                        datasets: [{
                            label: 'Transaction Count',
                            data: hourlyData.map(h => h.count),
                            backgroundColor: chartColors.info + '80',
                            borderColor: chartColors.info,
                            borderWidth: 1
                        }, {
                            label: 'Amount (₹)',
                            data: hourlyData.map(h => h.amount),
                            backgroundColor: chartColors.success + '80',
                            borderColor: chartColors.success,
                            borderWidth: 1,
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#1F2937' }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            }
                        }
                    }
                });
            }

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusSummary = @json($statusSummary ?? []);
                const statusData = {
                    labels: ['Success', 'Pending', 'Failed'],
                    datasets: [{
                        data: [
                            statusSummary.success?.count || 0,
                            statusSummary.pending?.count || 0,
                            statusSummary.failed?.count || 0
                        ],
                        backgroundColor: [chartColors.success, chartColors.warning, chartColors.danger],
                        borderColor: '#1F2937',
                        borderWidth: 2
                    }]
                };
                
                new Chart(statusCtx, {
                    type: 'pie',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#1F2937', padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Period-Based Chart
            let periodChartInstance = null;
            const periodCtx = document.getElementById('periodChart');
            if (periodCtx) {
                const periodData = analyticsData.periodAnalytics || {};
                
                function updatePeriodChart(period) {
                    let labels = [];
                    let amountData = [];
                    let countData = [];
                    
                    if (period === 'today' || period === 'yesterday') {
                        // Hourly data for today/yesterday
                        const data = periodData[period] || [];
                        const hourlyMap = {};
                        data.forEach(item => {
                            hourlyMap[item.hour] = item;
                        });
                        
                        for (let h = 0; h < 24; h++) {
                            labels.push(h + ':00');
                            const item = hourlyMap[h] || { count: 0, amount: 0 };
                            amountData.push(item.amount);
                            countData.push(item.count);
                        }
                    } else {
                        // Daily data for weekly/monthly
                        const data = periodData[period] || [];
                        labels = data.map(d => d.date);
                        amountData = data.map(d => d.amount);
                        countData = data.map(d => d.count);
                    }
                    
                    const chartData = {
                        labels: labels,
                        datasets: [{
                            label: 'Transaction Amount (₹)',
                            data: amountData,
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '40',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y',
                        }, {
                            label: 'Transaction Count',
                            data: countData,
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '40',
                            tension: 0.4,
                            yAxisID: 'y1',
                        }]
                    };
                    
                    if (periodChartInstance) {
                        periodChartInstance.data = chartData;
                        periodChartInstance.update();
                    } else {
                        periodChartInstance = new Chart(periodCtx, {
                            type: 'line',
                            data: chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        labels: { color: '#1F2937' }
                                    },
                                    title: {
                                        display: true,
                                        text: period.charAt(0).toUpperCase() + period.slice(1) + ' Transaction Analysis',
                                        color: '#1F2937',
                                        font: { size: 16 }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { color: '#1F2937' },
                                        grid: { color: 'rgba(0,0,0,0.1)' }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        beginAtZero: true,
                                        ticks: { color: '#1F2937' },
                                        grid: { drawOnChartArea: false }
                                    },
                                    x: {
                                        ticks: { color: '#1F2937' },
                                        grid: { color: 'rgba(0,0,0,0.1)' }
                                    }
                                }
                            }
                        });
                    }
                }
                
                // Initialize with today
                updatePeriodChart('today');
                
                // Period button handlers
                document.querySelectorAll('.period-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        const period = this.getAttribute('data-period');
                        updatePeriodChart(period);
                    });
                });
            }
        }
     </script>
@endsection


        const analyticsData = @json($analytics ?? []);
        
        // Chart Colors
        const chartColors = {
            primary: '#F15A22',
            success: '#10B981',
            warning: '#F59E0B',
            danger: '#EF4444',
            info: '#3B82F6',
            purple: '#8B5CF6',
            orange: '#F97316',
        };
        $(document).ready(function() {
            // Initialize Flatpickr for date inputs
            flatpickr("#from_date", {
                dateFormat: "Y-m-d"
            });
            
            flatpickr("#to_date", {
                dateFormat: "Y-m-d"
            });

            // Initialize DataTable (defensive init to prevent re-init errors)
            if ($('#payinReportTable').length) {
                // Remove empty state rows before initializing DataTables
                $('#payinReportTable tbody tr:has(td[colspan])').remove();
                
                // Check if table has actual data rows
                var hasData = $('#payinReportTable tbody tr').length > 0;
                
                if (hasData) {
                // Destroy existing if present
                if ($.fn.DataTable.isDataTable('#payinReportTable')) {
                    $('#payinReportTable').DataTable().destroy();
                }
                
                    // Initialize fresh - Simplified without Buttons extension
                    var table = $('#payinReportTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "ordering": true,
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        "pageLength": 10,
                        "order": [[11, "desc"]], // Sort by Date & Time descending
                    "language": {
                        "search": "Search transactions:",
                        "lengthMenu": "Show _MENU_ transactions per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ transactions",
                        "infoEmpty": "Showing 0 to 0 of 0 transactions",
                        "emptyTable": "No transactions found",
                        "zeroRecords": "No matching transactions found",
                        "paginate": {
                            "first": "«",
                            "last": "»",
                            "next": "›",
                            "previous": "‹"
                        }
                    },
                    "columnDefs": [
                            { "orderable": false, "targets": [0, 12] },
                            { "className": "text-center", "targets": [0, 6, 7, 9, 10, 11, 12] },
                            { "className": "text-right", "targets": [3, 4, 5] }
                        ],
                        "autoWidth": false,
                        "retrieve": true
                    });

                    // Update row numbers on order/search/draw
                    table.on('order.dt search.dt draw.dt', function () {
                        let start = table.page.info().start;
                        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                            cell.innerHTML = start + i + 1;
                        });
                    }).draw();
                }
            }
            
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        window.printTransaction = function(txnId) {
            if (!txnId) return;
            const url = `{{ url('/payment-success') }}?txn=${txnId}`;
            const printWindow = window.open(url, '_blank');
            if (!printWindow) {
                alert('Please allow popups to print the order.');
            }
        };

        // Initialize Charts after page loads and Chart.js is ready
        if (typeof Chart !== 'undefined') {
            initializeCharts();
        } else {
            // Wait for Chart.js to load
            window.addEventListener('load', function() {
                setTimeout(initializeCharts, 100);
            });
        }

        function initializeCharts() {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }
            
            console.log('Analytics Data:', analyticsData);
            
            // Daily Trends Chart
            const dailyTrendsCtx = document.getElementById('dailyTrendChart');
            if (dailyTrendsCtx) {
                const dailyData = analyticsData.dailyTrends || [];
                
                if (dailyData.length === 0) {
                    dailyTrendsCtx.parentElement.innerHTML = '<div style="color: #FFFFFF; text-align: center; padding: 2rem;">No data available for the selected period</div>';
                    return;
                }
                new Chart(dailyTrendsCtx, {
                    type: 'line',
                    data: {
                        labels: dailyData.map(d => d.date),
                        datasets: [{
                            label: 'Transaction Amount (₹)',
                            data: dailyData.map(d => d.amount),
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            tension: 0.4,
                            fill: true,
                        }, {
                            label: 'Transaction Count',
                            data: dailyData.map(d => d.count),
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '20',
                            tension: 0.4,
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#1F2937' }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            }
                        }
                    }
                });
            }

            // Payment Mode Distribution Chart
            const modeDistCtx = document.getElementById('modeDistributionChart');
            if (modeDistCtx) {
                const modeData = analyticsData.modeDistribution || {};
                const modes = Object.keys(modeData);
                
                if (modes.length === 0) {
                    modeDistCtx.parentElement.innerHTML = '<div style="color: #FFFFFF; text-align: center; padding: 2rem;">No payment mode data available</div>';
                    return;
                }
                
                const colors = [chartColors.primary, chartColors.info, chartColors.success, chartColors.warning, chartColors.purple, chartColors.orange];
                
                new Chart(modeDistCtx, {
                    type: 'doughnut',
                    data: {
                        labels: modes,
                        datasets: [{
                            data: modes.map(m => modeData[m].amount || 0),
                            backgroundColor: colors.slice(0, modes.length),
                            borderColor: '#1F2937',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#1F2937', padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const mode = context.label;
                                        const amount = context.parsed;
                                        const count = modeData[mode]?.count || 0;
                                        return `${mode}: ₹${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})} (${count} transactions)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Hourly Transaction Chart
            const hourlyCtx = document.getElementById('hourlyChart');
            if (hourlyCtx) {
                const hourlyData = analyticsData.hourlyData || [];
                
                if (!hourlyData || hourlyData.length === 0) {
                    hourlyCtx.parentElement.innerHTML = '<div style="color: #FFFFFF; text-align: center; padding: 2rem;">No hourly data available</div>';
                    return;
                }
                new Chart(hourlyCtx, {
                    type: 'bar',
                    data: {
                        labels: hourlyData.map(h => h.hour + ':00'),
                        datasets: [{
                            label: 'Transaction Count',
                            data: hourlyData.map(h => h.count),
                            backgroundColor: chartColors.info + '80',
                            borderColor: chartColors.info,
                            borderWidth: 1
                        }, {
                            label: 'Amount (₹)',
                            data: hourlyData.map(h => h.amount),
                            backgroundColor: chartColors.success + '80',
                            borderColor: chartColors.success,
                            borderWidth: 1,
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#1F2937' }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                ticks: { color: '#1F2937' },
                                grid: { drawOnChartArea: false }
                            },
                            x: {
                                ticks: { color: '#1F2937' },
                                grid: { color: 'rgba(0,0,0,0.1)' }
                            }
                        }
                    }
                });
            }

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusSummary = @json($statusSummary ?? []);
                const statusData = {
                    labels: ['Success', 'Pending', 'Failed'],
                    datasets: [{
                        data: [
                            statusSummary.success?.count || 0,
                            statusSummary.pending?.count || 0,
                            statusSummary.failed?.count || 0
                        ],
                        backgroundColor: [chartColors.success, chartColors.warning, chartColors.danger],
                        borderColor: '#1F2937',
                        borderWidth: 2
                    }]
                };
                
                new Chart(statusCtx, {
                    type: 'pie',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#1F2937', padding: 15 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Period-Based Chart
            let periodChartInstance = null;
            const periodCtx = document.getElementById('periodChart');
            if (periodCtx) {
                const periodData = analyticsData.periodAnalytics || {};
                
                function updatePeriodChart(period) {
                    let labels = [];
                    let amountData = [];
                    let countData = [];
                    
                    if (period === 'today' || period === 'yesterday') {
                        // Hourly data for today/yesterday
                        const data = periodData[period] || [];
                        const hourlyMap = {};
                        data.forEach(item => {
                            hourlyMap[item.hour] = item;
                        });
                        
                        for (let h = 0; h < 24; h++) {
                            labels.push(h + ':00');
                            const item = hourlyMap[h] || { count: 0, amount: 0 };
                            amountData.push(item.amount);
                            countData.push(item.count);
                        }
                    } else {
                        // Daily data for weekly/monthly
                        const data = periodData[period] || [];
                        labels = data.map(d => d.date);
                        amountData = data.map(d => d.amount);
                        countData = data.map(d => d.count);
                    }
                    
                    const chartData = {
                        labels: labels,
                        datasets: [{
                            label: 'Transaction Amount (₹)',
                            data: amountData,
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '40',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y',
                        }, {
                            label: 'Transaction Count',
                            data: countData,
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '40',
                            tension: 0.4,
                            yAxisID: 'y1',
                        }]
                    };
                    
                    if (periodChartInstance) {
                        periodChartInstance.data = chartData;
                        periodChartInstance.update();
                    } else {
                        periodChartInstance = new Chart(periodCtx, {
                            type: 'line',
                            data: chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        labels: { color: '#1F2937' }
                                    },
                                    title: {
                                        display: true,
                                        text: period.charAt(0).toUpperCase() + period.slice(1) + ' Transaction Analysis',
                                        color: '#1F2937',
                                        font: { size: 16 }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { color: '#1F2937' },
                                        grid: { color: 'rgba(0,0,0,0.1)' }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        beginAtZero: true,
                                        ticks: { color: '#1F2937' },
                                        grid: { drawOnChartArea: false }
                                    },
                                    x: {
                                        ticks: { color: '#1F2937' },
                                        grid: { color: 'rgba(0,0,0,0.1)' }
                                    }
                                }
                            }
                        });
                    }
                }
                
                // Initialize with today
                updatePeriodChart('today');
                
                // Period button handlers
                document.querySelectorAll('.period-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        const period = this.getAttribute('data-period');
                        updatePeriodChart(period);
                    });
                });
            }
        }
     </script>
@endsection

