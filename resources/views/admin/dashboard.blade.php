@extends('admin.layout.user')

@section('css')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    /* NEW COLOR THEME - Orange & Cream */
    :root {
        --primary-orange: #F15A22;
        --light-cream: #FFEDD5;
        --white: #FFFFFF;
        --text-dark: #1F2937;
        --text-light: #6B7280;
        --success-color: #F15A22;
        --danger-color: #DC2626;
        --warning-color: #F59E0B;
        --info-color: #F15A22;
        --light-bg: #FFEDD5;
        --card-shadow: 0 2px 8px rgba(241, 90, 34, 0.15);
    }
    
    .page-content {
        padding: 1rem !important;
        max-height: 100vh;
        overflow-y: auto;
        background: var(--light-cream) !important;
    }
    
    /* Compact Header */
    .dashboard-header {
        margin-bottom: 1rem;
        padding: 0.75rem 0;
        border-bottom: 3px solid var(--primary-orange);
    }
    
    .dashboard-header h4 {
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-dark);
        font-weight: 700;
    }

    /* Date/Time Chip Group */
    .dt-chip-group{display:flex;align-items:center;gap:.5rem}
    .dt-chip{display:inline-flex;align-items:center;gap:.4rem;background:#F3F4F6;color:#374151;border:1px solid #E5E7EB;border-radius:999px;padding:.35rem .6rem;font-size:.78rem;font-weight:600}
    .dt-chip .icon{width:14px;height:14px;display:inline-block;opacity:.7}
    .dt-sub{display:block;text-align:right;font-size:.7rem;color:#6B7280;margin-top:.15rem}
    
    /* Metric Cards - Orange Theme */
    .metric-card {
        background: var(--white);
        border-radius: 12px;
        padding: 1rem;
        box-shadow: var(--card-shadow);
        border-left: 4px solid var(--primary-orange);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(241, 90, 34, 0.25);
    }
    
    .metric-label {
        font-size: 0.75rem;
        color: var(--text-light);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    
    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-orange);
    }
    
    .metric-card.success { border-left-color: var(--primary-orange); }
    .metric-card.danger { border-left-color: var(--danger-color); }
    .metric-card.warning { border-left-color: var(--warning-color); }
    .metric-card.info { border-left-color: var(--primary-orange); }
    
    .metric-value.success { color: var(--primary-orange); }
    .metric-value.danger { color: var(--danger-color); }
    .metric-value.warning { color: var(--warning-color); }
    
    /* Analytics Grid */
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    
    /* Period Stats - Orange Theme */
    .period-stats {
        background: var(--white);
        border-radius: 12px;
        padding: 1rem;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(241, 90, 34, 0.2);
    }
    
    .period-header {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--primary-orange);
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-orange);
        text-transform: uppercase;
    }
    
    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.25rem 0;
        font-size: 0.75rem;
    }
    
    .stat-label {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: #6B7280;
        font-weight: 500;
    }
    
    .stat-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    .stat-dot.success { background: var(--primary-orange); }
    .stat-dot.danger { background: var(--danger-color); }
    .stat-dot.warning { background: var(--warning-color); }
    
    .stat-value {
        font-weight: 700;
        color: var(--text-dark);
    }
    
    .stat-count {
        color: var(--text-light);
        font-size: 0.7rem;
        margin-left: 0.25rem;
    }
    
    /* Section Title */
    .section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary-orange);
        margin: 0.75rem 0 0.5rem 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Comparison Card - Orange Theme NO GRADIENT */
    .comparison-card {
        background: var(--primary-orange);
        color: white;
        border-radius: 8px;
        padding: 0.75rem;
        box-shadow: var(--card-shadow);
    }
    
    .comparison-title {
        font-size: 0.75rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }
    
    .comparison-values {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }
    
    .comparison-item {
        flex: 1;
    }
    
    .comparison-label {
        font-size: 0.65rem;
        opacity: 0.8;
    }
    
    .comparison-amount {
        font-size: 1rem;
        font-weight: 700;
    }
    
    /* Responsive Grid */
    @media (max-width: 1400px) {
        .analytics-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 992px) {
        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Status Badge - Orange Theme */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .status-badge.success {
        background: rgba(241, 90, 34, 0.1);
        color: var(--primary-orange);
        border: 1px solid var(--primary-orange);
    }
    
    .status-badge.danger {
        background: rgba(220, 38, 38, 0.1);
        color: var(--danger-color);
        border: 1px solid var(--danger-color);
    }
    
    .status-badge.warning {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
        border: 1px solid var(--warning-color);
    }
    
    /* User Filter Section - Orange Theme */
    .filter-section {
        background: var(--white);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1rem;
        border: 1px solid rgba(241, 90, 34, 0.2);
    }
    
    .filter-section h6 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary-orange);
        margin-bottom: 0.75rem;
        text-transform: uppercase;
    }
    
    .filter-section .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }
    
    .filter-section .form-control {
        font-size: 0.8rem;
        padding: 0.5rem;
        border: 2px solid rgba(241, 90, 34, 0.3);
        border-radius: 6px;
    }
    
    .filter-section .btn {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
    }
    
    /* User Summary Table - Orange Theme */
    .summary-table-container {
        background: var(--white);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
        margin-top: 1rem;
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid rgba(241, 90, 34, 0.2);
    }
    
    .summary-table-container h6 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary-orange);
        margin-bottom: 1rem;
    }
    
    .summary-table {
        font-size: 0.8rem;
    }
    
    .summary-table thead th {
        background: var(--light-cream);
        color: var(--primary-orange);
        font-weight: 700;
        padding: 0.75rem;
        border-bottom: 2px solid var(--primary-orange);
    }
    
    .summary-table tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid rgba(241, 90, 34, 0.1);
        color: var(--text-dark);
    }
    
    .summary-table .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Chart Section - Orange Theme */
    .chart-section {
        background: var(--white);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1rem;
        border: 1px solid rgba(241, 90, 34, 0.2);
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--primary-orange);
    }
    
    .chart-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary-orange);
        text-transform: uppercase;
    }
    
    .chart-filters {
        display: flex;
        gap: 0.5rem;
    }
    
    .chart-filter-btn {
        padding: 0.4rem 0.9rem;
        border: 2px solid var(--primary-orange);
        background: var(--white);
        color: var(--primary-orange);
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        outline: none;
    }
    
    .chart-filter-btn:hover {
        background: var(--primary-orange);
        color: var(--white);
        transform: translateY(-2px);
    }
    
    .chart-filter-btn.active {
        background: var(--primary-orange);
        color: var(--white);
        border-color: var(--primary-orange);
    }
    
    .chart-filter-btn:focus {
        outline: 2px solid var(--primary-orange);
        outline-offset: 2px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 992px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4>Analytics Dashboard - {{ admin('name') }}</h4>
            <div class="text-end">
                <div class="dt-chip-group">
                    <span class="dt-chip" id="dt-date">
                        <span class="icon">📅</span>
                        <span id="chipDate">{{ now()->format('D, d M Y') }}</span>
                    </span>
                    <span class="dt-chip" id="dt-time">
                        <span class="icon">🕒</span>
                        <span id="chipTime">{{ now()->timezone('Asia/Kolkata')->format('h:i:s A') }}</span>
                    </span>
                    <span class="dt-chip" id="dt-tz">
                        <span class="icon">🌐</span>
                        <span>IST (UTC+5:30)</span>
                    </span>
                </div>
                <small class="dt-sub" id="dt-updated">Last updated: just now</small>
            </div>
        </div>
    </div>

        @if (admin('role') !== 'agent')
        <!-- Top Metrics - 6 Cards in 1 Row -->
        <div class="row g-2 mb-3">
            <div class="col-md-2">
                <div class="metric-card info">
                    <div class="metric-label">Today Users</div>
                    <div class="metric-value">+{{ $today_user }}</div>
                </div>
                    </div>
            <div class="col-md-2">
                <div class="metric-card">
                    <div class="metric-label">Total Users</div>
                    <div class="metric-value">{{ number_format($total_user) }}</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="metric-card success">
                    <div class="metric-label">Today Pay-in</div>
                    <div class="metric-value success">₹{{ number_format($mlmrevenue['todaypayin']/1000, 1) }}K</div>
                </div>
                    </div>
            <div class="col-md-2">
                <div class="metric-card danger">
                    <div class="metric-label">Today Pay-out</div>
                    <div class="metric-value danger">₹{{ number_format($mlmrevenue['todaypayout']/1000, 1) }}K</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="metric-card warning">
                    <div class="metric-label">Success Rate</div>
                    <div class="metric-value warning">{{ number_format($successRatio, 1) }}%</div>
                </div>
                    </div>
            <div class="col-md-2">
                <div class="metric-card">
                    <div class="metric-label">Server TPS</div>
                    <div class="metric-value">{{ $finalQPS }}</div>
                </div>
            </div>
        </div>

        <!-- Wallet Balances - 4 Cards in 1 Row -->
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-label">Pay-in Wallet</div>
                    <div class="metric-value">₹{{ number_format($padminamount, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-label">Pay-out Wallet</div>
                    <div class="metric-value">₹{{ number_format($payoutadminamount, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-label">Hold Wallet</div>
                    <div class="metric-value">₹{{ number_format($holdamount, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-label">AEPS Wallet</div>
                    <div class="metric-value">₹{{ number_format($adminamount, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Interactive Charts -->
        <div class="section-title">📊 Interactive Analytics Charts</div>
        <div class="charts-grid">
            <!-- Pay-in Chart -->
            <div class="chart-section">
                <div class="chart-header">
                    <h6 class="chart-title">📈 Pay-in Analytics</h6>
                    <div class="chart-filters">
                        <button type="button" class="chart-filter-btn active" data-period="today">Today</button>
                        <button type="button" class="chart-filter-btn" data-period="weekly">7 Days</button>
                        <button type="button" class="chart-filter-btn" data-period="monthly">Monthly</button>
                        <button type="button" class="chart-filter-btn" data-period="yearly">Yearly</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="payinChart"></canvas>
                </div>
            </div>
            
            <!-- Pay-out Chart -->
            <div class="chart-section">
                <div class="chart-header">
                    <h6 class="chart-title">💸 Pay-out Analytics</h6>
                    <div class="chart-filters">
                        <button type="button" class="chart-filter-btn active" data-period="today">Today</button>
                        <button type="button" class="chart-filter-btn" data-period="weekly">7 Days</button>
                        <button type="button" class="chart-filter-btn" data-period="monthly">Monthly</button>
                        <button type="button" class="chart-filter-btn" data-period="yearly">Yearly</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="payoutChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pay-in Analytics - 5 Periods in 1 Row -->
        <div class="section-title">📊 Pay-in Analytics Details</div>
        <div class="analytics-grid">
            @foreach ($stats as $period => $m)
            <div class="period-stats">
                <div class="period-header">{{ ucfirst($period) }}</div>
                <div class="stat-row">
                    <div class="stat-label">
                        <span class="stat-dot success"></span> Success
                    </div>
                    <div class="stat-value">
                        ₹{{ number_format($m['SumPayinSuccess'], 0) }}
                        <span class="stat-count">({{ $m['TotalPayinSuccess'] }})</span>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">
                        <span class="stat-dot danger"></span> Failed
            </div>
                    <div class="stat-value">
                        ₹{{ number_format($m['SumPayinFailed'], 0) }}
                        <span class="stat-count">({{ $m['TotalPayinFailed'] }})</span>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">
                        <span class="stat-dot warning"></span> Pending
            </div>
                    <div class="stat-value">
                        ₹{{ number_format($m['SumPayinPending'], 0) }}
                        <span class="stat-count">({{ $m['TotalPayinPending'] }})</span>
                    </div>
                </div>
            </div>
        @endforeach
        </div>

        <!-- Pay-out Analytics - 5 Periods in 1 Row -->
        <div class="section-title">💸 Pay-out Analytics</div>
        <div class="analytics-grid">
        @foreach ($stats as $period => $m)
            <div class="period-stats">
                <div class="period-header">{{ ucfirst($period) }}</div>
                <div class="stat-row">
                    <div class="stat-label">
                        <span class="stat-dot success"></span> Success
            </div>
                    <div class="stat-value">
                        ₹{{ number_format($m['SumPayoutSuccess'], 0) }}
                        <span class="stat-count">({{ $m['TotalPayoutSuccess'] }})</span>
    </div>
                                </div>
                <div class="stat-row">
                    <div class="stat-label">
                        <span class="stat-dot danger"></span> Failed
                                </div>
                    <div class="stat-value">
                        ₹{{ number_format($m['SumPayoutFailed'], 0) }}
                        <span class="stat-count">({{ $m['TotalPayoutFailed'] }})</span>
                                </div>
                            </div>
                <div class="stat-row">
                    <div class="stat-label">
                        <span class="stat-dot warning"></span> Pending
                    </div>
                    <div class="stat-value">
                        ₹{{ number_format($m['SumPayoutPending'], 0) }}
                        <span class="stat-count">({{ $m['TotalPayoutPending'] }})</span>
                    </div>
                </div>
            </div>
                                    @endforeach
        </div>

        <!-- User-wise Date Filter -->
        <div class="section-title">🔍 User-wise Analytics Filter</div>
        <div class="filter-section">
            <h6>Find User Wise Summary</h6>
            <form action="{{ url('/admin/dashboard') }}" method="GET" class="forms-sample">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">From Date:</label>
                        <input type="date" class="form-control" name="from" value="{{ request('from') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date:</label>
                        <input type="date" class="form-control" name="to" value="{{ request('to') }}" required>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Find Analytics
                        </button>
                        <a href="{{ url('/admin/dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- User Summary Table -->
            @if(isset($SummaryData) && $SummaryData)
        <div class="summary-table-container">
            <h6>📊 User Wise Summary ({{ request('from') }} to {{ request('to') }})</h6>
                        <div class="table-responsive">
                <table class="table summary-table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                            <th>User ID</th>
                                        <th>Name</th>
                            <th>Pay-in Analytics</th>
                            <th>Pay-out Analytics</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($SummaryData as $row)
                                    @if($row->SumPayinPending != 0 || $row->SumPayinSuccess !=0 || $row->SumPayinFailed !=0 ||$row->SumPayoutPending !=0||$row->SumPayoutSuccess !=0||$row->SumPayoutFailed !=0)
                                    <tr>
                            <td class="fw-bold">{{ $loop->iteration }}</td>
                            <td><span class="badge bg-primary">{{ $row->userid }}</span></td>
                            <td class="fw-semibold">{{ $row->name }}</td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        <span class="badge bg-success">Success</span> 
                                        ₹{{ number_format($row->SumPayinSuccess, 2) }} 
                                        <small class="text-muted">({{ $row->TotalPayinSuccess }})</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-danger">Failed</span> 
                                        ₹{{ number_format($row->SumPayinFailed, 2) }} 
                                        <small class="text-muted">({{ $row->TotalPayinFailed }})</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning">Pending</span> 
                                        ₹{{ number_format($row->SumPayinPending, 2) }} 
                                        <small class="text-muted">({{ $row->TotalPayinPending }})</small>
                                    </div>
                                </div>
                                        </td>
                                        <td>
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        <span class="badge bg-success">Success</span> 
                                        ₹{{ number_format($row->SumPayoutSuccess, 2) }} 
                                        <small class="text-muted">({{ $row->TotalPayoutSuccess }})</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-danger">Failed</span> 
                                        ₹{{ number_format($row->SumPayoutFailed, 2) }} 
                                        <small class="text-muted">({{ $row->TotalPayoutFailed }})</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning">Pending</span> 
                                        ₹{{ number_format($row->SumPayoutPending, 2) }} 
                                        <small class="text-muted">({{ $row->TotalPayoutPending }})</small>
                                    </div>
                                </div>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @php
                            $totalPayinSuccess = 0;
                            $totalPayinFailed = 0;
                            $totalPayinPending = 0;
                            $totalPayoutSuccess = 0;
                            $totalPayoutFailed = 0;
                            $totalPayoutPending = 0;
                            foreach($SummaryData as $row) {
                                if($row->SumPayinPending != 0 || $row->SumPayinSuccess !=0 || $row->SumPayinFailed !=0 ||$row->SumPayoutPending !=0||$row->SumPayoutSuccess !=0||$row->SumPayoutFailed !=0) {
                                    $totalPayinSuccess += $row->SumPayinSuccess;
                                    $totalPayinFailed += $row->SumPayinFailed;
                                    $totalPayinPending += $row->SumPayinPending;
                                    $totalPayoutSuccess += $row->SumPayoutSuccess;
                                    $totalPayoutFailed += $row->SumPayoutFailed;
                                    $totalPayoutPending += $row->SumPayoutPending;
                                }
                            }
                            $totalPayinAmount = $totalPayinSuccess + $totalPayinFailed + $totalPayinPending;
                            $totalPayoutAmount = $totalPayoutSuccess + $totalPayoutFailed + $totalPayoutPending;
                        @endphp
                        <div class="mt-3 pt-3 border-top" style="background-color: #FFFFFF; padding: 1rem; border-radius: 8px;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="mb-2" style="color: #000000 !important; font-weight: 700;">Total Pay-in Amount</h6>
                                    <div class="d-flex flex-column gap-1" style="color: #000000;">
                                        <div>
                                            <span class="badge bg-success">Success</span> 
                                            ₹{{ number_format($totalPayinSuccess, 2) }}
                                        </div>
                                        <div>
                                            <span class="badge bg-danger">Failed</span> 
                                            ₹{{ number_format($totalPayinFailed, 2) }}
                                        </div>
                                        <div>
                                            <span class="badge bg-warning">Pending</span> 
                                            ₹{{ number_format($totalPayinPending, 2) }}
                                        </div>
                                        <div class="mt-2 pt-2 border-top">
                                            <strong style="color: #000000;">Total: ₹{{ number_format($totalPayinAmount, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2" style="color: #000000 !important; font-weight: 700;">Total Pay-out Amount</h6>
                                    <div class="d-flex flex-column gap-1" style="color: #000000;">
                                        <div>
                                            <span class="badge bg-success">Success</span> 
                                            ₹{{ number_format($totalPayoutSuccess, 2) }}
                                        </div>
                                        <div>
                                            <span class="badge bg-danger">Failed</span> 
                                            ₹{{ number_format($totalPayoutFailed, 2) }}
                                        </div>
                                        <div>
                                            <span class="badge bg-warning">Pending</span> 
                                            ₹{{ number_format($totalPayoutPending, 2) }}
                                        </div>
                                        <div class="mt-2 pt-2 border-top">
                                            <strong style="color: #000000;">Total: ₹{{ number_format($totalPayoutAmount, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        @endif
    @else
        <div class="alert alert-warning">
            @if (admin('status') == 0)
                KYC pending – will be processed Monday 10am. Contact 7060471592.
            @else
                Agent Dashboard - Limited View
            @endif
        </div>
        
        <div class="row g-2">
            <div class="col-md-6">
                <div class="metric-card">
                    <div class="metric-label">Total Users</div>
                    <div class="metric-value">{{ number_format($total_user) }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="metric-card">
                    <div class="metric-label">Server TPS</div>
                    <div class="metric-value">{{ $finalQPS }}</div>
                </div>
            </div>
        </div>
    @endif
</div>

@section('js')
<script>
// Analytics data from Laravel
const analyticsData = @json($stats);

// Chart instances
let payinChart = null;
let payoutChart = null;

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    attachEventListeners();
    startHeaderClock();
});

function initializeCharts() {
    // Initialize Pay-in Chart
    const payinCtx = document.getElementById('payinChart');
    if (payinCtx) {
        payinChart = new Chart(payinCtx.getContext('2d'), {
            type: 'bar',
            data: getPayinChartData('today'),
            options: getChartOptions('Pay-in')
        });
    }
    
    // Initialize Pay-out Chart
    const payoutCtx = document.getElementById('payoutChart');
    if (payoutCtx) {
        payoutChart = new Chart(payoutCtx.getContext('2d'), {
            type: 'bar',
            data: getPayoutChartData('today'),
            options: getChartOptions('Pay-out')
        });
    }
}

// Live header clock (IST)
function startHeaderClock(){
    try{
        const dateEl = document.getElementById('chipDate');
        const timeEl = document.getElementById('chipTime');
        const updatedEl = document.getElementById('dt-updated');
        if(!dateEl || !timeEl) return;
        const fmtDate = (d)=> d.toLocaleDateString('en-IN',{weekday:'short', day:'2-digit', month:'short', year:'numeric'});
        const fmtTime = (d)=> d.toLocaleTimeString('en-IN',{hour12:true, hour:'2-digit', minute:'2-digit', second:'2-digit', timeZone:'Asia/Kolkata'});
        const tick = ()=>{
            const now = new Date();
            // Derive IST
            const ist = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Kolkata' }));
            dateEl.textContent = fmtDate(ist);
            timeEl.textContent = fmtTime(ist);
        };
        tick();
        setInterval(tick,1000);
        if(updatedEl){
            updatedEl.textContent = 'Last updated: just now';
        }
    }catch(e){ console.warn('Header clock init failed', e); }
}

function attachEventListeners() {
    // Pay-in chart buttons
    const payinButtons = document.querySelectorAll('#payinChart').length > 0 
        ? document.querySelector('#payinChart').closest('.chart-section').querySelectorAll('.chart-filter-btn')
        : [];
    
    payinButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.getAttribute('data-period');
            updatePayinChart(period);
        });
    });
    
    // Pay-out chart buttons
    const payoutButtons = document.querySelectorAll('#payoutChart').length > 0
        ? document.querySelector('#payoutChart').closest('.chart-section').querySelectorAll('.chart-filter-btn')
        : [];
    
    payoutButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.getAttribute('data-period');
            updatePayoutChart(period);
        });
    });
}

function getPayinChartData(period) {
    const data = analyticsData[period];
    return {
        labels: ['Success', 'Failed', 'Pending'],
        datasets: [{
            label: 'Amount (₹)',
            data: [
                data.SumPayinSuccess || 0,
                data.SumPayinFailed || 0,
                data.SumPayinPending || 0
            ],
            backgroundColor: [
                'rgba(241, 90, 34, 0.8)',   // Success - Orange
                'rgba(220, 38, 38, 0.8)',   // Failed - Red
                'rgba(245, 158, 11, 0.8)'   // Pending - Yellow
            ],
            borderColor: [
                'rgb(241, 90, 34)',
                'rgb(220, 38, 38)',
                'rgb(245, 158, 11)'
            ],
            borderWidth: 2,
            borderRadius: 6
        }]
    };
}

function getPayoutChartData(period) {
    const data = analyticsData[period];
    return {
        labels: ['Success', 'Failed', 'Pending'],
        datasets: [{
            label: 'Amount (₹)',
            data: [
                data.SumPayoutSuccess || 0,
                data.SumPayoutFailed || 0,
                data.SumPayoutPending || 0
            ],
            backgroundColor: [
                'rgba(241, 90, 34, 0.8)',
                'rgba(220, 38, 38, 0.8)',
                'rgba(245, 158, 11, 0.8)'
            ],
            borderColor: [
                'rgb(241, 90, 34)',
                'rgb(220, 38, 38)',
                'rgb(245, 158, 11)'
            ],
            borderWidth: 2,
            borderRadius: 6
        }]
    };
}

function getChartOptions(type) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed.y || 0;
                        const chartCanvas = context.chart.canvas;
                        const chartSection = chartCanvas.closest('.chart-section');
                        const activeBtn = chartSection.querySelector('.chart-filter-btn.active');
                        const period = activeBtn ? activeBtn.getAttribute('data-period') : 'today';
                        const data = analyticsData[period];
                        
                        let count = 0;
                        if (type === 'Pay-in') {
                            if (label === 'Success') count = data.TotalPayinSuccess || 0;
                            if (label === 'Failed') count = data.TotalPayinFailed || 0;
                            if (label === 'Pending') count = data.TotalPayinPending || 0;
                        } else {
                            if (label === 'Success') count = data.TotalPayoutSuccess || 0;
                            if (label === 'Failed') count = data.TotalPayoutFailed || 0;
                            if (label === 'Pending') count = data.TotalPayoutPending || 0;
                        }
                        
                        return `${label}: ₹${value.toLocaleString()} (${count} transactions)`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    };
}

function updatePayinChart(period) {
    // Update active button
    const chartSection = document.querySelector('#payinChart').closest('.chart-section');
    const buttons = chartSection.querySelectorAll('.chart-filter-btn');
    
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-period') === period) {
            btn.classList.add('active');
        }
    });
    
    // Update chart data
    payinChart.data = getPayinChartData(period);
    payinChart.update();
}

function updatePayoutChart(period) {
    // Update active button
    const chartSection = document.querySelector('#payoutChart').closest('.chart-section');
    const buttons = chartSection.querySelectorAll('.chart-filter-btn');
    
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-period') === period) {
            btn.classList.add('active');
        }
    });
    
    // Update chart data
    payoutChart.data = getPayoutChartData(period);
    payoutChart.update();
}

</script>
@endsection
@endsection