@extends('admin.layout.user')
@section('css')
<style>
    /* Light gradient theme */
    .card-header {
        background: #FFEDD5;
        color: #F15A22;
        border-bottom: none;
        padding: 1.5rem 2rem;
        border-radius: 10px 10px 0 0;
    }
    .card-header h6 {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1.4rem;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    /* Status summary cards with light colors */
    .status-summary {
        margin-bottom: 2rem;
    }
    .status-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border-left: 4px solid;
        transition: transform 0.2s ease;
        margin-bottom: 1rem;
    }
    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }
    .status-card.pending {
        border-left-color: #ff9800;
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    }
    .status-card.success {
        border-left-color: #F15A22;
        background: linear-gradient(135deg, #FFEDD5 0%, #FFEDD5 100%);
    }
    .status-card.failed {
        border-left-color: #f44336;
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    }

    .status-card .count {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .status-card .label {
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-card.pending .count {
        color: #ff9800;
    }
    .status-card.pending .label {
        color: #e65100;
    }
    .status-card.success .count {
        color: #F15A22;
    }
    .status-card.success .label {
        color: #D14A15;
    }
    .status-card.failed .count {
        color: #f44336;
    }
    .status-card.failed .label {
        color: #c62828;
    }
    
    /* Enhanced table styles */
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-top: 1rem;
    }
    .table-header-section {
        background: #FFEDD5;
        padding: 1.5rem;
        border-bottom: 1px solid #e0e0e0;
    }
    .table-header-section h6 {
        margin: 0;
        color: #424242;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .table thead th {
        background: #FFEDD5;
        color: #F15A22;
        font-weight: 700;
        border: none;
        padding: 1.2rem 1rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        text-align: center;
    }
    .table thead th:first-child {
        border-top-left-radius: 12px;
    }
    .table thead th:last-child {
        border-top-right-radius: 12px;
    }
    .table thead th::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(21, 101, 192, 0.3);
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f5f5f5;
        vertical-align: middle;
        transition: all 0.2s ease;
        text-align: center;
    }
    .table tbody td:first-child {
        text-align: center;
        font-weight: 700;
        color: #F15A22;
    }
    .table tbody td:nth-child(2),
    .table tbody td:nth-child(3) {
        text-align: left;
    }
    .table tbody td:nth-child(4) {
        text-align: right;
        font-weight: 700;
        color: #D14A15;
    }
    .table tbody td:nth-child(5),
    .table tbody td:nth-child(6),
    .table tbody td:nth-child(7) {
        text-align: left;
    }
    .table tbody td:nth-child(8) {
        text-align: center;
    }
    .table tbody td:nth-child(9),
    .table tbody td:nth-child(10),
    .table tbody td:nth-child(11) {
        text-align: center;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
    }
    .table tbody td:nth-child(12),
    .table tbody td:nth-child(13) {
        text-align: center;
        font-size: 0.85rem;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    .table tbody tr:hover {
        background: #FFFFFF;
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .table tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }
    .table tbody tr:nth-child(even):hover {
        background: #FFFFFF;
    }
    
    /* Enhanced status badges */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 100px;
        text-align: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .status-badge.pending {
        background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
    }
    .status-badge.success {
        background: linear-gradient(135deg, #F15A22 0%, #F15A22 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
    }
    .status-badge.failed {
        background: linear-gradient(135deg, #f44336 0%, #ef5350 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
    }
    
    /* Enhanced amount styling */
    .amount-credited {
        background: linear-gradient(135deg, #FFEDD5 0%, #FFEDD5 100%);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 700;
        color: #D14A15;
        border: 1px solid #FFEDD5;
        display: inline-block;
        min-width: 80px;
    }
    
    /* User info styling */
    .user-info {
        background: linear-gradient(135deg, #FFEDD5 0%, #FFEDD5 100%);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #F15A22;
        border: 1px solid #FFEDD5;
        display: inline-block;
        min-width: 100px;
    }
    
    /* Bank details styling */
    .bank-details {
        background: linear-gradient(135deg, #FFEDD5 0%, #FFEDD5 100%);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #F15A22;
        border: 1px solid #FFEDD5;
        display: inline-block;
        min-width: 100px;
    }
    
    /* Transaction details styling */
    .transaction-details {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #e65100;
        border: 1px solid #ffcc02;
        display: inline-block;
        min-width: 120px;
    }
    
    /* Date styling */
    .date-time {
        background: linear-gradient(135deg, #FFEDD5 0%, #FFEDD5 100%);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #F15A22;
        border: 1px solid #FFEDD5;
        display: inline-block;
        min-width: 120px;
    }
    
    /* Empty state styling */
    .empty-state {
        padding: 3rem 2rem;
        text-align: center;
        background: #FFFFFF;
    }
    .empty-state i {
        font-size: 4rem;
        color: #adb5bd;
        margin-bottom: 1rem;
    }
    .empty-state h5 {
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    .empty-state p {
        color: #adb5bd;
        margin-bottom: 0;
    }
    
    /* Pagination styling */
    .pagination {
        margin-top: 2rem;
        justify-content: center;
    }
    .page-link {
        color: #F15A22;
        border: 2px solid #F15A22;
        margin: 0 0.2rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    .page-link:hover {
        background: #FFEDD5;
        color: #F15A22;
        border-color: #F15A22;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #F15A22 0%, #D14A15 100%);
        border-color: #F15A22;
        color: white;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .status-summary .row > div {
            margin-bottom: 1rem;
        }
        .table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.85rem;
        }
        .table thead th {
            padding: 1rem 0.5rem;
            font-size: 0.8rem;
        }
        .status-badge {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            min-width: 80px;
        }
    }
    
    /* Analytics Dashboard Styles */
    .analytics-dashboard {
        margin: 2rem 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .time-filter-section {
        background: #FFFFFF;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .time-filter-section h6 {
        margin: 0 0 1rem 0;
        color: #F15A22;
        font-weight: 700;
        font-size: 1.2rem;
    }
    
    .time-filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .time-filter-btn {
        padding: 0.5rem 1rem;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        color: #666;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .time-filter-btn:hover {
        background: #FFEDD5;
        color: #F15A22;
        border-color: #F15A22;
        text-decoration: none;
    }
    
    .time-filter-btn.active {
        background: linear-gradient(135deg, #F15A22 0%, #D14A15 100%);
        color: white;
        border-color: #F15A22;
    }
    
    .overview-metrics {
        padding: 2rem;
        background: #FFFFFF;
    }
    
    .metric-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s ease;
    }
    
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }
    
    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        background: linear-gradient(135deg, #F15A22 0%, #D14A15 100%);
    }
    
    .metric-icon.success {
        background: linear-gradient(135deg, #F15A22 0%, #F15A22 100%);
    }
    
    .metric-icon.amount {
        background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%);
    }
    
    .metric-icon.rate {
        background: linear-gradient(135deg, #9c27b0 0%, #ba68c8 100%);
    }
    
    .metric-content {
        flex: 1;
    }
    
    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .metric-label {
        font-size: 0.9rem;
        color: #666;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .charts-section {
        padding: 2rem;
    }
    
    .chart-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    .chart-header {
        background: #FFFFFF;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .chart-header h6 {
        margin: 0;
        color: #F15A22;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .chart-type-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .chart-type-btn {
        padding: 0.4rem 0.8rem;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        color: #666;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .chart-type-btn:hover {
        background: #FFEDD5;
        color: #F15A22;
        border-color: #F15A22;
    }
    
    .chart-type-btn.active {
        background: linear-gradient(135deg, #F15A22 0%, #D14A15 100%);
        color: white;
        border-color: #F15A22;
    }
    
    .chart-body {
        padding: 1.5rem;
    }
    
    .recent-activity-list {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }
    
    /* Custom scrollbar styles */
    .recent-activity-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .recent-activity-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .recent-activity-list::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #F15A22 0%, #D14A15 100%);
        border-radius: 3px;
    }
    
    .recent-activity-list::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #0d47a1 0%, #F15A22 100%);
    }
    
    .activity-item {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
    }
    
    .activity-item:hover {
        background: #FFFFFF;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-time {
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .activity-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }
    
    .activity-user {
        font-weight: 700;
        color: #F15A22;
        font-size: 0.9rem;
    }
    
    .activity-amount {
        font-weight: 700;
        color: #D14A15;
        font-size: 0.9rem;
    }
    
    .activity-status {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-success {
        background: linear-gradient(135deg, #FFEDD5 0%, #FFEDD5 100%);
        color: #D14A15;
        border: 1px solid #FFEDD5;
    }
    
    .status-pending {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        color: #e65100;
        border: 1px solid #ffcc02;
    }
    
    .status-failed {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #c62828;
        border: 1px solid #ef9a9a;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .time-filter-buttons {
            flex-direction: column;
        }
        
        .time-filter-btn {
            justify-content: center;
        }
        
        .overview-metrics .row > div {
            margin-bottom: 1rem;
        }
        
        .metric-card {
            flex-direction: column;
            text-align: center;
        }
        
        .metric-icon {
            margin-bottom: 1rem;
        }
        
        .chart-type-buttons {
            flex-direction: column;
            margin-top: 1rem;
        }
        
        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .recent-activity-list {
            max-height: 300px;
        }
        
        .activity-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-money-bill-wave mr-2"></i>{{$title}}</h6>
                </div>
                
                <div class="card-body p-4">
                    <!-- Analytics Dashboard -->
                    <div class="analytics-dashboard">
                        <!-- Time Period Filter -->
                        <div class="time-filter-section">
                            <h6><i class="fas fa-chart-line mr-2"></i>Payout Analytics</h6>
                            <div class="time-filter-buttons">
                                <a href="{{ request()->fullUrlWithQuery(['period' => 'today']) }}" 
                                   class="time-filter-btn {{ $period == 'today' ? 'active' : '' }}">
                                    <i class="fas fa-calendar-day"></i> Today
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['period' => 'yesterday']) }}" 
                                   class="time-filter-btn {{ $period == 'yesterday' ? 'active' : '' }}">
                                    <i class="fas fa-calendar-minus"></i> Yesterday
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['period' => 'weekly']) }}" 
                                   class="time-filter-btn {{ $period == 'weekly' ? 'active' : '' }}">
                                    <i class="fas fa-calendar-week"></i> This Week
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['period' => 'monthly']) }}" 
                                   class="time-filter-btn {{ $period == 'monthly' ? 'active' : '' }}">
                                    <i class="fas fa-calendar-alt"></i> This Month
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['period' => 'yearly']) }}" 
                                   class="time-filter-btn {{ $period == 'yearly' ? 'active' : '' }}">
                                    <i class="fas fa-calendar"></i> This Year
                                </a>
                            </div>
                        </div>

                        <!-- Overview Metrics -->
                        <div class="overview-metrics">
                        <div class="row">
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                </div>
                                        <div class="metric-content">
                                            <div class="metric-value">{{ $analytics['overview']['total_payouts'] }}</div>
                                            <div class="metric-label">Total Payouts</div>
                            </div>
                                </div>
                            </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-icon success">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="metric-content">
                                            <div class="metric-value">{{ $analytics['overview']['successful_payouts'] }}</div>
                                            <div class="metric-label">Successful</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-icon amount">
                                            <i class="fas fa-rupee-sign"></i>
                                        </div>
                                        <div class="metric-content">
                                            <div class="metric-value">₹{{ number_format($analytics['overview']['total_amount'], 2) }}</div>
                                            <div class="metric-label">Total Amount</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-icon rate">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                        <div class="metric-content">
                                            <div class="metric-value">{{ $analytics['overview']['success_rate'] }}%</div>
                                            <div class="metric-label">Success Rate</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="charts-section">
                            <div class="row">
                                <!-- Transaction Trends Chart -->
                                <div class="col-md-8">
                                    <div class="chart-card">
                                        <div class="chart-header">
                                            <h6><i class="fas fa-chart-line mr-2"></i>Payout Trends</h6>
                                            <!-- Chart Type Buttons for Payout Trends -->
                                            <div class="chart-type-buttons float-right">
                                                <button class="chart-type-btn active" data-type="line">
                                                    <i class="fas fa-chart-line"></i> Line
                                                </button>
                                                <button class="chart-type-btn" data-type="bar">
                                                    <i class="fas fa-chart-bar"></i> Bar
                                                </button>
                                                <button class="chart-type-btn" data-type="histogram">
                                                    <i class="fas fa-chart-area"></i> Histogram
                                                </button>
                                            </div>
                                        </div>
                                        <div class="chart-body">
                                            <canvas id="trendsChart" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Distribution Chart -->
                            <div class="col-md-4">
                                    <div class="chart-card">
                                        <div class="chart-header">
                                            <h6><i class="fas fa-chart-pie mr-2"></i>Status Distribution</h6>
                                        </div>
                                        <div class="chart-body">
                                            <canvas id="statusChart" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Hourly Activity Chart -->
                                <div class="col-md-6">
                                    <div class="chart-card">
                                        <div class="chart-header">
                                            <h6><i class="fas fa-clock mr-2"></i>Hourly Activity</h6>
                                        </div>
                                        <div class="chart-body">
                                            <canvas id="hourlyChart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gateway Stats Chart -->
                                <div class="col-md-6">
                                    <div class="chart-card">
                                        <div class="chart-header">
                                            <h6><i class="fas fa-network-wired mr-2"></i>Gateway Statistics</h6>
                                        </div>
                                        <div class="chart-body">
                                            <canvas id="gatewayChart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Users and Recent Activity -->
                        <div class="row">
                            <!-- Top Users by Payouts -->
                            <div class="col-md-6">
                                <div class="chart-card">
                                    <div class="chart-header">
                                        <h6><i class="fas fa-users mr-2"></i>Top Users by Payouts</h6>
                                    </div>
                                    <div class="chart-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>User ID</th>
                                                        <th>Payouts</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($analytics['top_users_by_count'] as $user)
                                                    <tr>
                                                        <td>{{ $user->userid }}</td>
                                                        <td>{{ $user->payout_count }}</td>
                                                        <td>₹{{ number_format($user->total_amount, 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            <div class="col-md-6">
                                <div class="chart-card">
                                    <div class="chart-header">
                                        <h6><i class="fas fa-history mr-2"></i>Recent Activity <small class="text-muted">(Scrollable)</small></h6>
                                    </div>
                                    <div class="chart-body">
                                        <div class="recent-activity-list">
                                            @foreach($analytics['recent_activity'] as $activity)
                                            <div class="activity-item">
                                                <div class="activity-time">{{ dformat($activity->created_at, 'd-m-Y H:i:s') }}</div>
                                                <div class="activity-details">
                                                    <span class="activity-user">{{ $activity->userid }}</span>
                                                    <span class="activity-amount">₹{{ number_format($activity->amount, 2) }}</span>
                                                    <span class="activity-status status-{{ $activity->status == 1 ? 'success' : ($activity->status == 0 ? 'pending' : 'failed') }}">
                                                        {{ $activity->status == 1 ? 'Success' : ($activity->status == 0 ? 'Pending' : 'Failed') }}
                                                    </span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Table -->
                    <div class="table-container">
                        <div class="table-header-section">
                            <h6><i class="fas fa-table mr-2"></i>Payout Ledger Details</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User ID</th>
                                        <th>Username</th>
                                        <th>Amount Credited</th>
                                        <th>Holder Name</th>
                                        <th>Account No.</th>
                                        <th>IFSC Code</th>
                                        <th>Status</th>
                                        <th>UTR</th>
                                        <th>Txn ID</th>
                                        <th>Bank Txn ID</th>
                                        <th>Created At</th>
                                        <th>Callback At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($data) > 0)
                                        @foreach ($data as $item)
                                            <tr id="table{{ $item->id }}">
                                                <td>{{ $item->id }}</td>
                                                <td>
                                                    <span class="user-info">{{$item->userid}}</span>
                                                </td>
                                                <td>
                                                    <span class="user-info">{{userbyuserid($item->userid,'name')}}</span>
                                                </td>
                                                <td>
                                                    <span class="amount-credited">+{{ balance($item->amount) }}</span>
                                                </td>
                                                <td>
                                                    <span class="bank-details">{{ ($item->holder_name) }}</span>
                                                </td>
                                                <td>
                                                    <span class="bank-details">{{ ($item->account_no) }}</span>
                                                </td>
                                                <td>
                                                    <span class="bank-details">{{ ($item->ifsc_code) }}</span>
                                                </td>
                                                <td>
                                                    @if($item->status == 0)
                                                        <span class="status-badge pending">
                                                            <i class="fas fa-clock"></i>Pending
                                                        </span>
                                                    @elseif($item->status == 1)
                                                        <span class="status-badge success">
                                                            <i class="fas fa-check-circle"></i>Success
                                                        </span>
                                                    @else
                                                        <span class="status-badge failed">
                                                            <i class="fas fa-times-circle"></i>Failed
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="transaction-details">{{ ($item->utr) }}</span>
                                                </td>
                                                <td>
                                                    <span class="transaction-details">{{$item->transaction_id}}</span>
                                                </td>
                                                <td>
                                                    <span class="transaction-details">{{$item->txnid2}}</span>
                                                </td>
                                                <td>
                                                    <span class="date-time">
                                                        <i class="far fa-clock mr-1"></i>
                                                        {{ dformat($item->created_at,'d-m-Y H:i:s') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="date-time">
                                                        <i class="far fa-clock mr-1"></i>
                                                        {{ dformat($item->updated_at,'d-m-Y H:i:s') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="13">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox"></i>
                                                    <h5>No {{$title}} Found</h5>
                                                    <p>No payout records available at the moment.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    @if(count($data) > 0)
                        <div class="d-flex justify-content-center">
                            {{ $data->links('pagination::simple-bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<script>
    // Analytics data from PHP
    const analyticsData = @json($analytics);
    
    // Chart instances
    let trendsChart = null;
    let statusChart = null;
    let hourlyChart = null;
    let gatewayChart = null;
    
    // Initialize all charts
    document.addEventListener('DOMContentLoaded', function() {
        initializeTrendsChart();
        initializeStatusChart();
        initializeHourlyChart();
        initializeGatewayChart();
        
        // Chart type switching for trends chart
        document.querySelectorAll('.chart-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const chartType = this.dataset.type;
                
                // Update active button
                document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Update chart type
                updateTrendsChartType(chartType);
            });
        });
    });
    
    function initializeTrendsChart() {
        const ctx = document.getElementById('trendsChart').getContext('2d');
        const dailyData = analyticsData.daily_stats;
        const labels = dailyData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        trendsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Payouts',
                        data: dailyData.map(item => item.payouts),
                        borderColor: '#64b5f6',
                        backgroundColor: 'rgba(100, 181, 246, 0.3)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    },
                    {
                        label: 'Successful',
                        data: dailyData.map(item => item.successful),
                        borderColor: '#81c784',
                        backgroundColor: 'rgba(129, 199, 132, 0.3)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    },
                    {
                        label: 'Amount (₹)',
                        data: dailyData.map(item => item.amount),
                        borderColor: '#ffb74d',
                        backgroundColor: 'rgba(255, 183, 77, 0.3)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Payout Count'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
    
    function updateTrendsChartType(chartType) {
        if (!trendsChart) return;
        
        const dailyData = analyticsData.daily_stats;
        const labels = dailyData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        let chartConfig = {
            type: chartType,
            data: {
                labels: labels,
                datasets: []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Payout Count'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        };
        
        if (chartType === 'line') {
            chartConfig.data.datasets = [
                {
                    label: 'Total Payouts',
                    data: dailyData.map(item => item.payouts),
                    borderColor: '#64b5f6',
                    backgroundColor: 'rgba(100, 181, 246, 0.3)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                },
                {
                    label: 'Successful',
                    data: dailyData.map(item => item.successful),
                    borderColor: '#81c784',
                    backgroundColor: 'rgba(129, 199, 132, 0.3)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                },
                {
                    label: 'Amount (₹)',
                    data: dailyData.map(item => item.amount),
                    borderColor: '#ffb74d',
                    backgroundColor: 'rgba(255, 183, 77, 0.3)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    yAxisID: 'y1'
                }
            ];
        } else if (chartType === 'bar') {
            chartConfig.data.datasets = [
                {
                    label: 'Total Payouts',
                    data: dailyData.map(item => item.payouts),
                    backgroundColor: 'rgba(100, 181, 246, 0.9)',
                    borderColor: '#64b5f6',
                    borderWidth: 2
                },
                {
                    label: 'Successful',
                    data: dailyData.map(item => item.successful),
                    backgroundColor: 'rgba(129, 199, 132, 0.9)',
                    borderColor: '#81c784',
                    borderWidth: 2
                },
                {
                    label: 'Amount (₹)',
                    data: dailyData.map(item => item.amount),
                    backgroundColor: 'rgba(255, 183, 77, 0.9)',
                    borderColor: '#ffb74d',
                    borderWidth: 2,
                    yAxisID: 'y1'
                }
            ];
        } else if (chartType === 'histogram') {
            chartConfig.type = 'bar';
            chartConfig.data.datasets = [
                {
                    label: 'Total Payouts',
                    data: dailyData.map(item => item.payouts),
                    backgroundColor: 'rgba(100, 181, 246, 0.9)',
                    borderColor: '#64b5f6',
                    borderWidth: 2
                },
                {
                    label: 'Successful',
                    data: dailyData.map(item => item.successful),
                    backgroundColor: 'rgba(129, 199, 132, 0.9)',
                    borderColor: '#81c784',
                    borderWidth: 2
                },
                {
                    label: 'Pending',
                    data: dailyData.map(item => item.pending),
                    backgroundColor: 'rgba(255, 193, 7, 0.9)',
                    borderColor: '#ff8f00',
                    borderWidth: 2
                },
                {
                    label: 'Failed',
                    data: dailyData.map(item => item.failed),
                    backgroundColor: 'rgba(244, 67, 54, 0.9)',
                    borderColor: '#d32f2f',
                    borderWidth: 2
                }
            ];
        }
        
        trendsChart.destroy();
        trendsChart = new Chart(document.getElementById('trendsChart').getContext('2d'), chartConfig);
    }
    
    function initializeStatusChart() {
        const ctx = document.getElementById('statusChart').getContext('2d');
        
        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Success', 'Pending', 'Failed'],
                datasets: [{
                    data: [
                        analyticsData.status_distribution.success,
                        analyticsData.status_distribution.pending,
                        analyticsData.status_distribution.failed
                    ],
                    backgroundColor: [
                        '#FFEDD5',
                        '#fff9c4',
                        '#ffcdd2'
                    ],
                    borderColor: [
                        '#FFEDD5',
                        '#fff176',
                        '#ef9a9a'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
    
    function initializeHourlyChart() {
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyData = analyticsData.hourly_stats;
        
        hourlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: hourlyData.map(item => item.hour + ':00'),
                datasets: [
                    {
                        label: 'Payouts',
                        data: hourlyData.map(item => item.payouts),
                        backgroundColor: 'rgba(100, 181, 246, 0.9)',
                        borderColor: '#64b5f6',
                        borderWidth: 2
                    },
                    {
                        label: 'Amount (₹)',
                        data: hourlyData.map(item => item.amount),
                        backgroundColor: 'rgba(129, 199, 132, 0.9)',
                        borderColor: '#81c784',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Payout Count'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
    
    function initializeGatewayChart() {
        const ctx = document.getElementById('gatewayChart').getContext('2d');
        const gatewayData = analyticsData.gateway_stats;
        
        gatewayChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: gatewayData.map(item => item.gateway || 'Unknown'),
                datasets: [
                    {
                        label: 'Payout Count',
                        data: gatewayData.map(item => item.count),
                        backgroundColor: 'rgba(156, 39, 176, 0.9)',
                        borderColor: '#9c27b0',
                        borderWidth: 2
                    },
                    {
                        label: 'Amount (₹)',
                        data: gatewayData.map(item => item.amount),
                        backgroundColor: 'rgba(255, 152, 0, 0.9)',
                        borderColor: '#ff9800',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        type: 'linear',
                        display: true,
                        position: 'bottom',
                        title: {
                            display: true,
                            text: 'Payout Count'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'top',
                        title: {
                            display: true,
                            text: 'Amount (₹)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
    
    function PayoutApi(id){
        let UTR = $("#Payout"+id+"").val();
        location.href="/admin/api/approvedPayOut/"+id+"/"+UTR;
    }
    
    function PayoutApiDecline(id) {
        // Prompt the user to enter a message
        let message = prompt("Please enter the reason for declining this payout:");

        // Check if the user entered a message or canceled
        if (message !== null && message.trim() !== "") {
            // Redirect to the API with the entered message
            location.href = `/admin/api/deniedPayOut/${id}/${encodeURIComponent(message)}`;
        } else {
            // Show an alert if the user didn't enter a valid message
            alert("Decline message is required!");
        }
    }
</script>
@endsection