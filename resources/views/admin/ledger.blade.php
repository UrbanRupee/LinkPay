@extends('admin.layout.user')
@section('css')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Chart.js Date Adapter -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<style>
    /* ORANGE THEME - NO GRADIENTS */
    .card-header {
        background: #FFEDD5 !important;
        color: #F15A22 !important;
        border-bottom: 3px solid #F15A22 !important;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
    }
    .card-header h6 {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 1.4rem;
        color: #F15A22 !important;
    }
    
    /* Status summary cards - ORANGE THEME */
    .status-summary {
        margin-bottom: 2rem;
    }
    .status-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(241, 90, 34, 0.15);
        border-left: 4px solid;
        transition: transform 0.2s ease;
        margin-bottom: 1rem;
    }
    .status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(241, 90, 34, 0.25);
    }
    .status-card.pending {
        border-left-color: #F59E0B;
        background: #FFFFFF;
    }
    .status-card.success {
        border-left-color: #F15A22;
        background: #FFFFFF;
    }
    .status-card.failed {
        border-left-color: #DC2626;
        background: #FFFFFF;
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
        color: #F59E0B;
    }
    .status-card.pending .label {
        color: #D97706;
    }
    .status-card.success .count {
        color: #F15A22;
    }
    .status-card.success .label {
        color: #D14A15;
    }
    .status-card.failed .count {
        color: #DC2626;
    }
    .status-card.failed .label {
        color: #B91C1C;
    }
    
    /* Enhanced table styles - ORANGE THEME */
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(241, 90, 34, 0.15);
        overflow: hidden;
        margin-top: 1rem;
        border: 1px solid rgba(241, 90, 34, 0.2);
    }
    .table-header-section {
        background: #FFEDD5;
        padding: 1.5rem;
        border-bottom: 2px solid #F15A22;
    }
    .table-header-section h6 {
        margin: 0;
        color: #F15A22;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .table thead th {
        background: #FFEDD5 !important;
        color: #F15A22 !important;
        font-weight: 700;
        border: none;
        padding: 1.2rem 1rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        text-align: center;
    }

    .ledger-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .ledger-table thead th {
        background: #FFEDD5 !important;
        color: #F15A22 !important;
        font-weight: 700;
        border: none;
        padding: 1.2rem 1rem;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        text-align: center;
    }

    .ledger-table tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid #E5E7EB;
        vertical-align: top;
        font-size: 0.9rem;
        color: #1F2937;
        text-align: left;
        line-height: 1.45;
        white-space: normal;
    }

    .ledger-table tbody td.text-center {
        text-align: center !important;
    }

    .ledger-table tbody td.text-right {
        text-align: right !important;
    }

    .ledger-table tbody tr:hover {
        background: #F9FAFB;
    }

    .ledger-meta-list {
        list-style: none;
        margin: 0;
        padding: 0;
        color: #4B5563;
    }

    .ledger-meta-list li + li {
        margin-top: 0.3rem;
    }

    .badge-mode {
        display: inline-flex;
        gap: 0.35rem;
        align-items: center;
        background: #FFF7ED;
        color: #F97316;
        border-radius: 999px;
        padding: 0.2rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Pastel ledger styling overrides */
    .ledger-table tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid #E5E7EB;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #1F2937;
    }

    .ledger-table tbody tr:hover {
        background: #F9FAFB;
    }

    .ledger-table tbody td.amount-col {
        font-weight: 600;
    }

    .ledger-table tbody td.fees-col {
        color: #DC2626;
    }

    .ledger-table tbody td.settled-col {
        color: #047857;
        font-weight: 600;
    }

    .badge-mode {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #EEF2FF;
        color: #3730A3;
    }

    .badge-mode.upi {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .badge-mode.net-banking {
        background: #E0F2FE;
        color: #0369A1;
    }

    .badge-mode.card {
        background: #FCE7F3;
        color: #BE185D;
    }

    .badge-mode.admin-transfer {
        background: #FEF3C7;
        color: #92400E;
        border: 1px solid #F59E0B;
        font-weight: 700;
    }

    .payment-detail-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 999px;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        color: #1F2937;
        margin: 0.1rem 0.3rem 0.1rem 0;
    }

    .ledger-payment-details {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-badge.pending {
        background: #FEF3C7;
        color: #B45309;
    }

    .status-badge.success {
        background: #DCFCE7;
        color: #15803D;
    }

    .status-badge.failed {
        background: #FEE2E2;
        color: #B91C1C;
    }

    .action-btn {
        border: 1px solid #D1D5DB;
        background: #F9FAFB;
        color: #1F2937;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
    }

    .action-btn:hover {
        background: #E5E7EB;
    }
 
    .amount-credit {
        font-weight: 600;
        color: #111827;
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
        background: #F15A22;
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
        text-align: center;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
    }
    .table tbody td:nth-child(5) {
        text-align: right;
        font-weight: 700;
    }
    .table tbody td:nth-child(6) {
        text-align: center;
    }
    .table tbody td:nth-child(7) {
        text-align: center;
        font-size: 0.85rem;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    .table tbody tr:hover {
        background: rgba(241, 90, 34, 0.05);
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(241, 90, 34, 0.2);
    }
    .table tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }
    .table tbody tr:nth-child(even):hover {
        background: rgba(241, 90, 34, 0.05);
    }
    
    /* Enhanced status badges - ORANGE THEME */
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
        box-shadow: 0 2px 8px rgba(241, 90, 34, 0.15);
    }
    .status-badge.pending {
        background: #F59E0B;
        color: white;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }
    .status-badge.success {
        background: #F15A22;
        color: white;
        box-shadow: 0 4px 12px rgba(241, 90, 34, 0.4);
    }
    .status-badge.failed {
        background: #DC2626;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    }
    
    /* Enhanced amount styling - ORANGE THEME */
    .amount-credited {
        background: #FFFFFF;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 700;
        color: #F15A22;
        border: 2px solid #F15A22;
        display: inline-block;
        min-width: 80px;
    }
    
    /* User info styling - ORANGE THEME */
    .user-info {
        background: #FFFFFF;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #1F2937;
        border: 2px solid #F15A22;
        display: inline-block;
        min-width: 100px;
    }
    
    /* Transaction ID styling - ORANGE THEME */
    .transaction-id {
        background: #FFEDD5;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #F15A22;
        border: 1px solid #F15A22;
        display: inline-block;
        min-width: 120px;
        font-family: 'Courier New', monospace;
    }
    
    /* Date styling - ORANGE THEME */
    .date-time {
        background: #FFFFFF;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #1F2937;
        border: 1px solid rgba(241, 90, 34, 0.3);
        display: inline-block;
        min-width: 120px;
    }
    
    /* Empty state styling - ORANGE THEME */
    .empty-state {
        padding: 3rem 2rem;
        text-align: center;
        background: #FFFFFF;
        border: 2px dashed #F15A22;
        border-radius: 12px;
    }
    .empty-state i {
        font-size: 4rem;
        color: #F15A22;
        margin-bottom: 1rem;
    }
    .empty-state h5 {
        color: #F15A22;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    .empty-state p {
        color: #6B7280;
        margin-bottom: 0;
    }
    
    /* Pagination styling - ORANGE THEME */
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
        font-weight: 600;
    }
    .page-link:hover {
        background: rgba(241, 90, 34, 0.1);
        color: #F15A22;
        border-color: #F15A22;
    }
    .page-item.active .page-link {
        background: #F15A22;
        border-color: #F15A22;
        color: white;
    }
    
    /* Analytics Dashboard Styles */
    .analytics-section {
        margin-bottom: 2rem;
    }
    
    .analytics-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        border-left: 4px solid #F15A22;
    }
    
    .analytics-card h6 {
        color: #F15A22;
        font-weight: 700;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
    
    .metric-card {
        background: #FFFFFF;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
    }
    
    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #F15A22;
        margin-bottom: 0.5rem;
    }
    
    .metric-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 1rem;
    }
    
    .chart-container canvas {
        max-height: 300px;
    }
    
    .analytics-tabs {
        margin-bottom: 1.5rem;
    }
    
    .analytics-tabs .nav-link {
        color: #F15A22;
        font-weight: 600;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }
    
    .analytics-tabs .nav-link.active {
        color: #F15A22;
        border-bottom-color: #F15A22;
        background: none;
    }
    
    .analytics-tabs .nav-link:hover {
        border-bottom-color: #F15A22;
        background: rgba(21, 101, 192, 0.1);
    }
    
    .top-users-table {
        font-size: 0.9rem;
    }
    
    .top-users-table th {
        background: #FFEDD5;
        color: #F15A22;
        font-weight: 700;
        border: none;
        padding: 0.75rem;
    }
    
    .top-users-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #f5f5f5;
        vertical-align: middle;
    }
    
    .user-rank {
        background: linear-gradient(135deg, #F15A22 0%, #D14A15 100%);
        color: white;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
    }
    
    .gateway-badge {
        background: linear-gradient(135deg, #F15A22 0%, #F15A22 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .time-filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    .time-filter-btn {
        background: #FFFFFF;
        color: #6c757d;
        border: 2px solid #F15A22;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .time-filter-btn:hover {
        background: #FFEDD5;
        color: #F15A22;
        border-color: #F15A22;
        transform: translateY(-1px);
    }
    
    .time-filter-btn.active {
        background: linear-gradient(135deg, #F15A22 0%, #D14A15 100%);
        color: white;
        border-color: #F15A22;
        box-shadow: 0 4px 12px rgba(21, 101, 192, 0.4);
    }
    
    .chart-type-buttons {
        display: inline-flex;
        gap: 0.25rem;
        margin-left: 1rem;
    }
    
    .chart-type-btn {
        background: #FFFFFF;
        color: #6c757d;
        border: 1px solid #dee2e6;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.8rem;
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
    
    .recent-activity-item {
        background: #FFFFFF;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 3px solid #F15A22;
    }
    
    .recent-activity-item .activity-time {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
    }
    
    .recent-activity-item .activity-details {
        font-weight: 600;
        color: #495057;
        margin-top: 0.25rem;
    }
    
    .recent-activity-list {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }
    
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
        
        .analytics-card {
            padding: 1rem;
        }
        
        .chart-container {
            height: 250px;
        }
        
        .metric-value {
            font-size: 1.5rem;
        }
        
        .recent-activity-list {
            max-height: 300px;
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
                    <h6><i class="fas fa-book mr-2"></i>{{$title}}</h6>
                </div>
                
                <div class="card-body p-4">
                    <!-- Analytics Dashboard -->
                    <div class="analytics-section">
                        <!-- Time Period Filter -->
                        <div class="analytics-card mb-4">
                            <h6><i class="fas fa-calendar-alt mr-2"></i>Analytics Dashboard</h6>
                            <div class="time-filter-buttons">
                                <button class="time-filter-btn {{ $period == 'today' ? 'active' : '' }}" data-period="today">Today</button>
                                <button class="time-filter-btn {{ $period == 'yesterday' ? 'active' : '' }}" data-period="yesterday">Yesterday</button>
                                <button class="time-filter-btn {{ $period == 'weekly' ? 'active' : '' }}" data-period="weekly">This Week</button>
                                <button class="time-filter-btn {{ $period == 'monthly' ? 'active' : '' }}" data-period="monthly">This Month</button>
                                <button class="time-filter-btn {{ $period == 'yearly' ? 'active' : '' }}" data-period="yearly">This Year</button>
                                </div>
                            </div>
                        
                        <!-- Overview Metrics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <div class="metric-value" id="totalTransactions">{{ number_format($analytics['overview']['total_transactions']) }}</div>
                                    <div class="metric-label">Total Transactions</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <div class="metric-value" id="successfulTransactions">{{ number_format($analytics['overview']['successful_transactions']) }}</div>
                                    <div class="metric-label">Successful</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <div class="metric-value" id="totalAmount">₹{{ number_format($analytics['overview']['total_amount'], 2) }}</div>
                                    <div class="metric-label">Total Amount</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="metric-card">
                                    <div class="metric-value" id="successRate">{{ $analytics['overview']['success_rate'] }}%</div>
                                    <div class="metric-label">Success Rate</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Charts Row -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="analytics-card">
                                    <h6><i class="fas fa-chart-line mr-2"></i>Transaction Trends
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
                                    </h6>
                                    <div class="chart-container">
                                        <canvas id="trendsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="analytics-card">
                                    <h6><i class="fas fa-chart-pie mr-2"></i>Status Distribution</h6>
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                        
                        <!-- Second Row Charts -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="analytics-card">
                                    <h6><i class="fas fa-chart-bar mr-2"></i>Hourly Activity</h6>
                                    <div class="chart-container">
                                        <canvas id="hourlyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="analytics-card">
                                    <h6><i class="fas fa-credit-card mr-2"></i>Gateway Performance</h6>
                                    <div class="chart-container">
                                        <canvas id="gatewayChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Top Users and Recent Activity -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="analytics-card">
                                    <h6><i class="fas fa-trophy mr-2"></i>Top Users by Transactions</h6>
                                    <div class="table-responsive">
                                        <table class="table top-users-table">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>User ID</th>
                                                    <th>Transactions</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($analytics['top_users_by_count'] as $index => $user)
                                                <tr>
                                                    <td><div class="user-rank">{{ $index + 1 }}</div></td>
                                                    <td><strong>{{ $user->userid }}</strong></td>
                                                    <td>{{ $user->transaction_count }}</td>
                                                    <td>₹{{ number_format($user->total_amount, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="analytics-card">
                                    <h6><i class="fas fa-history mr-2"></i>Recent Activity 
                                        <small class="text-muted ml-2">
                                            <i class="fas fa-arrows-alt-v"></i> Scrollable
                                        </small>
                                    </h6>
                                    <div class="recent-activity-list">
                                        @foreach($analytics['recent_activity'] as $activity)
                                        <div class="recent-activity-item">
                                            <div class="activity-time">
                                                <i class="far fa-clock mr-1"></i>{{ dformat($activity->created_at, 'd-m-Y H:i:s') }}
                                            </div>
                                            <div class="activity-details">
                                                <strong>{{ $activity->userid }}</strong> - 
                                                ₹{{ number_format($activity->amount, 2) }} - 
                                                @if($activity->status == 1)
                                                    <span class="text-success">Success</span>
                                                @elseif($activity->status == 0)
                                                    <span class="text-warning">Pending</span>
                                                @else
                                                    <span class="text-danger">Failed</span>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Section -->
                    <div class="card mb-4" style="background: #FFEDD5; border: 2px solid #F15A22; border-radius: 12px;">
                        <div class="card-body p-4">
                            <h6 class="mb-3" style="color: #F15A22; font-weight: 700;">
                                <i class="fas fa-filter mr-2"></i>Filter Transactions
                            </h6>
                            <form method="GET" action="{{ url()->current() }}" id="filterForm">
                                <div class="row">
                                    <!-- Status Filter -->
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #1F2937;">Status</label>
                                        <select name="status_filter" class="form-control" style="border: 2px solid #F15A22;">
                                            <option value="">All Status</option>
                                            <option value="success" {{ request('status_filter') == 'success' ? 'selected' : '' }}>Success</option>
                                            <option value="pending" {{ request('status_filter') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="failed" {{ request('status_filter') == 'failed' ? 'selected' : '' }}>Failed</option>
                                        </select>
                                    </div>
                                    
                                    <!-- User ID Filter -->
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #1F2937;">User ID</label>
                                        <input type="text" name="userid" class="form-control" placeholder="Enter User ID" 
                                               value="{{ request('userid') }}" style="border: 2px solid #F15A22;">
                                    </div>
                                    
                                    <!-- Name Filter -->
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #1F2937;">Name</label>
                                        <input type="text" name="name_filter" class="form-control" placeholder="Enter Name" 
                                               value="{{ request('name_filter') }}" style="border: 2px solid #F15A22;">
                                    </div>
                                    
                                    <!-- Reference/Order ID/Gateway TXN Filter -->
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #1F2937;">Reference/Order ID</label>
                                        <input type="text" name="reference_filter" class="form-control" placeholder="Txn ID/Order ID/UTR" 
                                               value="{{ request('reference_filter') }}" style="border: 2px solid #F15A22;">
                                    </div>
                                    
                                    <!-- Payment Gateway Filter -->
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #1F2937;">Gateway</label>
                                        <select name="gateway_filter" class="form-control" style="border: 2px solid #F15A22;">
                                            <option value="">All Gateways</option>
                                            @if(isset($gateways))
                                                @foreach($gateways as $gatewayId => $gatewayName)
                                                    <option value="{{ $gatewayId }}" {{ request('gateway_filter') == $gatewayId ? 'selected' : '' }}>
                                                        {{ $gatewayName }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    
                                    <!-- Date Range Filters -->
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #1F2937;">From Date</label>
                                        <input type="date" name="from_date" class="form-control" 
                                               value="{{ request('from_date') }}" style="border: 2px solid #F15A22;">
                                    </div>
                                    
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #1F2937;">To Date</label>
                                        <input type="date" name="to_date" class="form-control" 
                                               value="{{ request('to_date') }}" style="border: 2px solid #F15A22;">
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="col-md-12 mb-2">
                                        <button type="submit" class="btn btn-primary" style="background: #F15A22; border: none; padding: 0.5rem 2rem; font-weight: 600;">
                                            <i class="fas fa-search mr-2"></i>Apply Filters
                                        </button>
                                        <a href="{{ url()->current() }}" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 0.5rem 2rem; font-weight: 600; margin-left: 0.5rem;">
                                            <i class="fas fa-redo mr-2"></i>Reset
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Preserve period parameter -->
                                @if(request('period'))
                                    <input type="hidden" name="period" value="{{ request('period') }}">
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Summary Totals Section -->
                    @if(isset($summaryTotals) && ($summaryTotals->success_amount > 0 || $summaryTotals->pending_amount > 0 || $summaryTotals->failed_amount > 0))
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div style="background: #FFFFFF; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(241, 90, 34, 0.15); border: 1px solid rgba(241, 90, 34, 0.2);">
                                <h6 style="color: #000000; font-weight: 700; margin-bottom: 1rem; font-size: 1.1rem;">📊 Filter Summary Totals</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div style="background: #FFFFFF; border-left: 4px solid #F15A22; padding: 1rem; border-radius: 6px;">
                                            <div style="font-size: 0.85rem; color: #6B7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Success Amount</div>
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #F15A22;">
                                                ₹{{ number_format($summaryTotals->success_amount ?? 0, 2) }}
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                                ({{ number_format($summaryTotals->success_count ?? 0) }} transactions)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div style="background: #FFFFFF; border-left: 4px solid #F59E0B; padding: 1rem; border-radius: 6px;">
                                            <div style="font-size: 0.85rem; color: #6B7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Pending Amount</div>
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #F59E0B;">
                                                ₹{{ number_format($summaryTotals->pending_amount ?? 0, 2) }}
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                                ({{ number_format($summaryTotals->pending_count ?? 0) }} transactions)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div style="background: #FFFFFF; border-left: 4px solid #DC2626; padding: 1rem; border-radius: 6px;">
                                            <div style="font-size: 0.85rem; color: #6B7280; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;">Failed Amount</div>
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #DC2626;">
                                                ₹{{ number_format($summaryTotals->failed_amount ?? 0, 2) }}
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6B7280; margin-top: 0.25rem;">
                                                ({{ number_format($summaryTotals->failed_count ?? 0) }} transactions)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Enhanced Table -->
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table ledger-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User ID</th>
                                        <th>Username</th>
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
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($data) > 0)
                                        @foreach ($data as $item)
                                            @php
                                                $isAdminTransaction = isset($item->is_admin_transaction) && $item->is_admin_transaction;
                                                $meta = $item->gateway_meta ?? [];
                                                
                                                if ($isAdminTransaction) {
                                                    // Admin transaction display
                                                    $reference = $item->transaction_id ?? 'ADMIN_' . $item->id;
                                                    $gatewayTxn = 'Admin Transfer';
                                                    $fees = 0;
                                                    $settled = (float) ($item->amount ?? 0);
                                                    $paymentDetails = [
                                                        'Type: ' . ucfirst($item->type ?? 'N/A'),
                                                        'Category: ' . ucfirst(str_replace('_', ' ', $item->category ?? 'N/A')),
                                                        'Wallet: ' . ucfirst($item->data2 ?? 'N/A'),
                                                        'Description: ' . ($item->data1 ?? 'Admin Transaction')
                                                    ];
                                                } else {
                                                    // Payment request display
                                                $reference = $meta['reference'] ?? $item->transaction_id;
                                                $gatewayTxn = $meta['gateway_txn'] ?? ($meta['reference'] ?? $item->transaction_id);
                                                $fees = $meta['fees'] ?? (float) ($item->tax ?? 0);
                                                $settled = $meta['settled'] ?? max(($item->amount ?? 0) - ($item->tax ?? 0), 0);
                                                $paymentDetails = $meta['payment_details'] ?? [];
                                                }
                                                
                                                $payloadJson = null;
                                                if (!empty($item->callback_payload)) {
                                                    $payloadJson = is_string($item->callback_payload)
                                                        ? $item->callback_payload
                                                        : json_encode($item->callback_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                                }
                                            @endphp
                                            <tr id="table{{ $item->id }}">
                                                <td class="text-center">{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                                <td>
                                                    <span class="user-info">{{$item->userid}}</span>
                                                </td>
                                                <td>
                                                    <span class="user-info">{{userbyuserid($item->userid,'name')}}</span>
                                                </td>
                                                <td><strong>{{ $reference }}</strong></td>
                                                <td>{{ $gatewayTxn ?? '—' }}</td>
                                                <td class="text-right amount-col">₹{{ number_format($item->amount, 2) }}</td>
                                                <td class="text-right fees-col">₹{{ number_format($fees, 2) }}</td>
                                                <td class="text-right settled-col">₹{{ number_format($settled, 2) }}</td>
                                                <td>
                                                    @if($isAdminTransaction)
                                                        <span class="badge-mode admin-transfer">
                                                            <i class="fas fa-exchange-alt"></i>Admin {{ ucfirst($item->type ?? 'Transfer') }}
                                                        </span>
                                                    @elseif(!empty($meta['mode']) && $meta['mode'] !== '—')
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
                                                        <div class="ledger-payment-details">
                                                            @foreach($paymentDetails as $line)
                                                                <span class="payment-detail-tag"><i class="fas fa-info-circle"></i>{{ $line }}</span>
                                                            @endforeach
                                                        </div>
                                                     @else
                                                         <span class="text-muted">—</span>
                                                     @endif
                                                </td>
                                                <td class="text-center">
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
                                                    @if($isAdminTransaction)
                                                        {{ $item->data1 ?? 'Admin Transaction' }}
                                                    @else
                                                        {{ ($meta['utr'] ?? $item->data4 ?? $item->data1) ?: '—' }}
                                                    @endif
                                                </td>
                                                <td class="text-center" data-order="{{ optional($item->created_at)->timestamp ?? 0 }}">
                                                    <span class="date-time">
                                                        <i class="far fa-clock mr-1"></i>
                                                        {{ dformat($item->created_at,'d-m-Y H:i:s') }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($payloadJson)
                                                        <button type="button" class="action-btn view-callback" data-payload="{{ base64_encode($payloadJson) }}" title="View callback payload">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="15">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox"></i>
                                                    <h5>No {{$title}} Found</h5>
                                                    <p>No transaction records available at the moment.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal fade" id="callbackPayloadModal" tabindex="-1" role="dialog" aria-labelledby="callbackPayloadModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="callbackPayloadModalLabel"><i class="fas fa-file-alt mr-2"></i>Gateway Callback Payload</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <pre id="callbackPayloadContent" class="bg-light p-3 rounded" style="max-height: 450px; overflow:auto;"></pre>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
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
@endsection

@section('js')
<script>
// Analytics data from PHP
const analyticsData = @json($analytics);

// Chart instances
let trendsChart, statusChart, hourlyChart, gatewayChart;
let currentChartType = 'line';

// Initialize all charts
function initializeCharts() {
    initTrendsChart();
    initStatusChart();
    initHourlyChart();
    initGatewayChart();
    setupChartTypeButtons();
}

// Trends chart (line chart)
function initTrendsChart() {
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
                    label: 'Total Transactions',
                    data: dailyData.map(item => item.transactions),
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
                        text: 'Transaction Count'
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
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Setup chart type buttons
function setupChartTypeButtons() {
    const chartTypeButtons = document.querySelectorAll('.chart-type-btn');
    
    chartTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            chartTypeButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const chartType = this.dataset.type;
            currentChartType = chartType;
            updateTrendsChart(chartType);
        });
    });
}

// Update trends chart based on type
function updateTrendsChart(chartType) {
    if (trendsChart) {
        trendsChart.destroy();
    }
    
    const ctx = document.getElementById('trendsChart').getContext('2d');
    const dailyData = analyticsData.daily_stats;
    const labels = dailyData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    let chartConfig = {
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Transactions',
                    data: dailyData.map(item => item.transactions),
                    borderColor: '#0d47a1',
                    backgroundColor: 'rgba(13, 71, 161, 0.3)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                },
                {
                    label: 'Successful',
                    data: dailyData.map(item => item.successful),
                    borderColor: '#D14A15',
                    backgroundColor: 'rgba(46, 125, 50, 0.3)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                },
                {
                    label: 'Amount (₹)',
                    data: dailyData.map(item => item.amount),
                    borderColor: '#e65100',
                    backgroundColor: 'rgba(230, 81, 0, 0.3)',
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
                        text: 'Transaction Count'
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
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    };
    
    // Modify config based on chart type
    if (chartType === 'bar') {
        chartConfig.type = 'bar';
        chartConfig.data.datasets.forEach(dataset => {
            dataset.tension = 0;
            dataset.fill = false;
        });
    } else if (chartType === 'histogram') {
        chartConfig.type = 'bar';
        chartConfig.data.datasets = [
            {
                label: 'Total Transactions',
                data: dailyData.map(item => item.transactions),
                backgroundColor: 'rgba(13, 71, 161, 0.9)',
                borderColor: '#0d47a1',
                borderWidth: 2
            },
            {
                label: 'Successful',
                data: dailyData.map(item => item.successful),
                backgroundColor: 'rgba(46, 125, 50, 0.9)',
                borderColor: '#D14A15',
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
        chartConfig.options.scales.y1.display = false;
    } else {
        chartConfig.type = 'line';
    }
    
    trendsChart = new Chart(ctx, chartConfig);
}

// Status distribution chart (doughnut)
function initStatusChart() {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Successful', 'Pending', 'Failed'],
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

// Hourly activity chart (bar chart)
function initHourlyChart() {
    const ctx = document.getElementById('hourlyChart').getContext('2d');
    
    const hourlyData = analyticsData.hourly_stats;
    const labels = hourlyData.map(item => {
        const hour = item.hour;
        return hour === 0 ? '12 AM' : 
               hour < 12 ? hour + ' AM' : 
               hour === 12 ? '12 PM' : 
               (hour - 12) + ' PM';
    });
    
    hourlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Transactions',
                    data: hourlyData.map(item => item.transactions),
                    backgroundColor: 'rgba(13, 71, 161, 0.9)',
                    borderColor: '#0d47a1',
                    borderWidth: 2
                },
                {
                    label: 'Amount (₹)',
                    data: hourlyData.map(item => item.amount),
                    backgroundColor: 'rgba(46, 125, 50, 0.9)',
                    borderColor: '#D14A15',
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
                        text: 'Transaction Count'
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

// Gateway performance chart (horizontal bar)
function initGatewayChart() {
    const ctx = document.getElementById('gatewayChart').getContext('2d');
    
    const gatewayData = analyticsData.gateway_stats;
    
    gatewayChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: gatewayData.map(item => `Gateway ${item.gateway}`),
            datasets: [{
                label: 'Transaction Count',
                data: gatewayData.map(item => item.count),
                backgroundColor: [
                    '#F15A22',
                    '#F15A22',
                    '#ff9800',
                    '#9c27b0',
                    '#f44336',
                    '#00bcd4',
                    '#795548',
                    '#607d8b'
                ],
                borderColor: [
                    '#0d47a1',
                    '#D14A15',
                    '#e65100',
                    '#6a1b9a',
                    '#c62828',
                    '#00838f',
                    '#5d4037',
                    '#455a64'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Transaction Count'
                    }
                }
            }
        }
    });
}

// Time filter functionality
function setupTimeFilters() {
    const filterButtons = document.querySelectorAll('.time-filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const period = this.dataset.period;
            updateAnalyticsForPeriod(period);
        });
    });
}

// Update analytics based on selected time period
function updateAnalyticsForPeriod(period) {
    console.log('Filtering analytics for period:', period);
    
    // Reload the page with the selected period parameter
    const url = new URL(window.location);
    url.searchParams.set('period', period);
    window.location.href = url.toString();
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupTimeFilters();

    $(document).on('click', '.view-callback', function() {
        const payload = $(this).data('payload');
        let formatted = 'No data available.';

        if (payload) {
            try {
                const decoded = atob(payload);
                const json = JSON.parse(decoded);
                formatted = JSON.stringify(json, null, 2);
            } catch (e) {
                try {
                    formatted = atob(payload);
                } catch (err) {
                    formatted = payload;
                }
            }
        }

        $('#callbackPayloadContent').text(formatted);
        $('#callbackPayloadModal').modal('show');
    });
});
</script>
@endsection