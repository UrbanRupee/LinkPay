@extends('user.layout.NewUser')

@section('css')
    <style>
        /* RED & BLACK THEME - PayIn Report */
        .container-fluid {
            background: var(--black) !important;
            min-height: 100vh;
        }
        
        .card-header {
            background: var(--black);
            color: #FFFFFF;
            border-bottom: 2px solid var(--primary-red);
            padding: 1.5rem 2rem;
            border-radius: 10px 10px 0 0;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 700;
            font-size: 1.4rem;
            color: #FFFFFF;
        }
        
        /* Status summary cards */
        .status-summary {
            margin-bottom: 2rem;
        }
        .status-card {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            border-left: 4px solid;
            border: 1px solid var(--medium-gray);
            transition: transform 0.2s ease;
        }
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.3);
        }
        .status-card.success {
            border-left-color: var(--primary-red);
        }
        .status-card.failed {
            border-left-color: var(--dark-red);
        }
        .status-card.pending {
            border-left-color: var(--light-red);
        }
        .status-card .count {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #FFFFFF !important;
        }
        .status-card .label {
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #FFFFFF !important;
        }
        
        /* Override Bootstrap text-* classes for status cards */
        .status-card .text-success,
        .status-card .text-warning,
        .status-card .text-danger,
        .status-card .text-info {
            color: #FFFFFF !important;
        }
        
        /* Status filter buttons */
        .status-filters {
            margin-bottom: 1.5rem;
        }
        .status-filter-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
            border: 2px solid var(--primary-red);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            background: var(--dark-gray);
            color: #FFFFFF !important;
        }
        .status-filter-btn:hover {
            background: var(--primary-red);
            color: #FFFFFF !important;
            transform: translateY(-2px);
            text-decoration: none;
        }
        .status-filter-btn.active {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
            background: var(--primary-red);
            color: #FFFFFF !important;
        }
        .status-filter-btn.all {
            background: var(--primary-red);
            color: #FFFFFF !important;
            border-color: var(--primary-red);
        }
        .status-filter-btn.all:hover {
            background: var(--dark-red);
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .status-filter-btn.success {
            background: var(--dark-gray);
            color: #FFFFFF !important;
            border-color: var(--primary-red);
        }
        .status-filter-btn.success:hover,
        .status-filter-btn.success.active {
            background: var(--primary-red);
            color: white;
            transform: translateY(-2px);
        }
        .status-filter-btn.failed {
            background: var(--dark-gray);
            color: #FFFFFF !important;
            border-color: var(--dark-red);
        }
        .status-filter-btn.failed:hover,
        .status-filter-btn.failed.active {
            background: var(--dark-red);
            color: #FFFFFF !important;
            transform: translateY(-2px);
        }
        .status-filter-btn.pending {
            background: var(--dark-gray);
            color: #FFFFFF !important;
            border-color: var(--light-red);
        }
        .status-filter-btn.pending:hover,
        .status-filter-btn.pending.active {
            background: var(--light-red);
            color: var(--black) !important;
            transform: translateY(-2px);
        }
        
        /* Enhanced table styles */
        .table-container {
            background: var(--dark-gray);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            overflow: hidden;
            margin-top: 1rem;
            border: 1px solid var(--medium-gray);
        }
        .table-header-section {
            background: var(--black);
            padding: 1.5rem;
            border-bottom: 2px solid var(--primary-red);
        }
        .table-header-section h6 {
            margin: 0;
            color: var(--primary-text-color);
            font-weight: 700;
            font-size: 1.1rem;
        }
        .export-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .export-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: 1px solid;
        }
        .export-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }
        .export-btn.copy {
            background: var(--dark-gray);
            color: var(--primary-red);
            border-color: var(--primary-red);
        }
        .export-btn.copy:hover {
            background: var(--primary-red);
            color: white;
        }
        .export-btn.csv {
            background: var(--dark-gray);
            color: var(--primary-red);
            border-color: var(--primary-red);
        }
        .export-btn.csv:hover {
            background: var(--primary-red);
            color: white;
        }
        .export-btn.excel {
            background: var(--dark-gray);
            color: var(--primary-red);
            border-color: var(--primary-red);
        }
        .export-btn.excel:hover {
            background: var(--primary-red);
            color: white;
        }
        .export-btn.pdf {
            background: var(--dark-gray);
            color: var(--dark-red);
            border-color: var(--dark-red);
        }
        .export-btn.pdf:hover {
            background: var(--dark-red);
            color: white;
        }
        .export-btn.print {
            background: var(--dark-gray);
            color: var(--light-red);
            border-color: var(--light-red);
        }
        .export-btn.print:hover {
            background: var(--light-red);
            color: var(--black);
        }
        
        .table thead th {
            background: var(--black);
            color: var(--primary-red);
            font-weight: 700;
            border: none;
            padding: 1.2rem 1.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
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
            background: rgba(255,255,255,0.3);
        }
        .table tbody td {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
            transition: all 0.2s ease;
        }
        .table tbody tr {
            transition: all 0.2s ease;
        }
        .table tbody tr:hover {
            background: rgba(220, 38, 38, 0.1);
            transform: scale(1.005);
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
        }
        .table tbody tr:nth-child(even) {
            background-color: rgba(220, 38, 38, 0.05);
        }
        .table tbody tr:nth-child(even):hover {
            background: rgba(220, 38, 38, 0.1);
        }
        .table tbody td {
            color: var(--primary-text-color);
        }
        
        /* Enhanced status badges */
        .status-badge {
            padding: 0.6rem 1.2rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 120px;
            text-align: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .status-badge.success {
            background: var(--primary-red);
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        }
        .status-badge.failed {
            background: var(--dark-red);
            color: white;
            box-shadow: 0 4px 12px rgba(153, 27, 27, 0.4);
        }
        .status-badge.pending {
            background: var(--light-red);
            color: var(--black);
            box-shadow: 0 4px 12px rgba(252, 165, 165, 0.4);
        }
        
        /* Enhanced amount styling */
        .amount-success {
            color: var(--primary-red);
            font-weight: 700;
            font-size: 1.1rem;
        }
        .amount-failed {
            color: var(--dark-red);
            font-weight: 700;
            font-size: 1.1rem;
        }
        .amount-pending {
            color: var(--light-red);
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        /* Transaction ID styling */
        .transaction-id {
            background: var(--medium-gray);
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            color: var(--primary-text-color);
            font-weight: 600;
            border: 1px solid var(--primary-red);
        }
        
        /* UTR styling */
        .utr-number {
            background: var(--medium-gray);
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary-red);
            border: 1px solid var(--primary-red);
        }
        
        /* Date styling */
        .date-time {
            background: var(--medium-gray);
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            color: var(--light-red);
            border: 1px solid var(--light-red);
        }
        
        /* Empty state styling */
        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
            background: var(--dark-gray);
        }
        .empty-state i {
            font-size: 4rem;
            color: var(--primary-red);
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .empty-state h5 {
            color: var(--primary-text-color);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: #9CA3AF;
            margin-bottom: 0;
        }
        
        /* DataTable customization */
        .dataTables_wrapper .dataTables_length select {
            border: 2px solid var(--primary-red);
            border-radius: 6px;
            padding: 0.5rem;
            background: var(--medium-gray);
            color: var(--primary-text-color);
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid var(--primary-red);
            border-radius: 6px;
            padding: 0.5rem 1rem;
            background: var(--medium-gray);
            color: var(--primary-text-color);
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25);
        }
        .dataTables_wrapper .dataTables_info {
            color: var(--primary-text-color);
            font-weight: 600;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px;
            margin: 0 0.2rem;
            border: 1px solid var(--primary-red);
            background: var(--dark-gray);
            color: var(--primary-text-color) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--primary-red) !important;
            color: white !important;
            border-color: var(--primary-red);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-red) !important;
            color: white !important;
            border-color: var(--primary-red);
        }
        
        /* Enhanced filter form */
        .filter-section {
            background: var(--dark-gray);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            border: 1px solid var(--medium-gray);
        }
        .filter-section h6 {
            color: var(--primary-red);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        .filter-form .form-control {
            border: 2px solid var(--primary-red);
            border-radius: 8px;
            background: var(--medium-gray);
            color: var(--primary-text-color);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .filter-form .form-control:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25);
            background: var(--dark-gray);
        }
        .filter-form .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .filter-form .btn-primary {
            background: var(--primary-red);
            border: none;
        }
        .filter-form .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
            background: var(--dark-red);
        }
        .filter-form .btn-secondary {
            background: var(--medium-gray);
            border: 1px solid var(--primary-red);
            color: var(--primary-text-color);
        }
        .filter-form .btn-secondary:hover {
            background: var(--dark-gray);
        }
        .filter-form label {
            color: #FFFFFF;
            font-weight: 600;
        }
        
        /* Ensure filter section headings are white */
        .filter-section h6,
        .status-filters h6 {
            color: #FFFFFF !important;
            font-weight: 600;
        }
        
        /* Make sure select/option elements are visible */
        select.form-control,
        select.form-select {
            background-color: var(--medium-gray) !important;
            border-color: var(--primary-red) !important;
            color: #FFFFFF !important;
        }
        
        select.form-control option,
        select.form-select option {
            background-color: var(--dark-gray) !important;
            color: #FFFFFF !important;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .status-summary .row > div {
                margin-bottom: 1rem;
            }
            .filter-form .col-md-4 {
                margin-bottom: 1rem;
            }
            .status-filter-btn {
                margin-right: 0.5rem;
                margin-bottom: 0.5rem;
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-chart-line mr-2"></i>{{ $title }}</h5>
                    <button class="btn btn-light btn-sm d-none" id="exportDataBtn">
                        <i class="fas fa-download mr-1"></i>Export Data
                    </button>
                </div>
                
                <div class="card-body p-4">
                    <!-- Status Summary Cards -->
                    <div class="status-summary">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="status-card success">
                                    <div class="count text-success">{{ $list->where('status', 1)->count() }}</div>
                                    <div class="label text-success">Successful Transactions</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-card pending">
                                    <div class="count text-warning">{{ $list->where('status', 0)->count() }}</div>
                                    <div class="label text-warning">Pending Transactions</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-card failed">
                                    <div class="count text-danger">{{ $list->where('status', '!=', 1)->where('status', '!=', 0)->count() }}</div>
                                    <div class="label text-danger">Failed Transactions</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter Buttons -->
                    <div class="status-filters">
                        <h6 class="mb-3"><i class="fas fa-filter mr-2"></i>Filter by Status:</h6>
                        <a href="{{ url()->current() }}" class="status-filter-btn all {{ !request('status_filter') ? 'active' : '' }}">
                            <i class="fas fa-list mr-1"></i>All Transactions
                        </a>
                        <a href="{{ url()->current() }}?status_filter=success" class="status-filter-btn success {{ request('status_filter') == 'success' ? 'active' : '' }}">
                            <i class="fas fa-check-circle mr-1"></i>Success Only
                        </a>
                        <a href="{{ url()->current() }}?status_filter=pending" class="status-filter-btn pending {{ request('status_filter') == 'pending' ? 'active' : '' }}">
                            <i class="fas fa-clock mr-1"></i>Pending Only
                        </a>
                        <a href="{{ url()->current() }}?status_filter=failed" class="status-filter-btn failed {{ request('status_filter') == 'failed' ? 'active' : '' }}">
                            <i class="fas fa-times-circle mr-1"></i>Failed Only
                        </a>
                    </div>

                    <!-- Enhanced Filter Form -->
                    <div class="filter-section">
                        <h6><i class="fas fa-calendar-alt mr-2"></i>Filter by Date & Search</h6>
                        <form action="{{ url()->current() }}" method="GET" class="filter-form">
                            @if(request('status_filter'))
                                <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
                            @endif
                            <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                    <label for="from_date" class="form-label fw-bold">From Date:</label>
                                <input type="text" class="form-control flatpickr-input" id="from_date" name="from_date" value="{{ request('from_date') }}" placeholder="Select From Date">
                            </div>
                            <div class="col-md-4">
                                    <label for="to_date" class="form-label fw-bold">To Date:</label>
                                <input type="text" class="form-control flatpickr-input" id="to_date" name="to_date" value="{{ request('to_date') }}" placeholder="Select To Date">
                            </div>
                                <div class="col-md-4">
                                    <label for="search" class="form-label fw-bold">Search:</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Transaction ID or UTR No.">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search mr-1"></i>Apply Filters
                                    </button>
                                    <a href="{{ url()->current() }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i>Clear All Filters
                                    </a>
                                </div>
                            </div>
                        </form>
                            </div>

                    <!-- Enhanced Table -->
                    <div class="table-container">
                        <div class="table-header-section">
                            <h6><i class="fas fa-table mr-2"></i>Payin Ledger Details</h6>
                            </div>
                        <div class="export-buttons">
                            <a href="#" class="export-btn copy" onclick="copyTableData()">
                                <i class="fas fa-copy"></i> Copy
                            </a>
                            <a href="{{ route('user.export.payin_report') }}?{{ http_build_query(request()->all()) }}" class="export-btn csv">
                                <i class="fas fa-file-csv"></i> CSV
                            </a>
                            <a href="{{ route('user.export.payin_report') }}?{{ http_build_query(request()->all()) }}" class="export-btn excel">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <a href="#" class="export-btn pdf" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="#" class="export-btn print" onclick="printTable()">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </div>
                    <div class="table-responsive">
                            <table class="table table-hover dataTable" id="reportTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                        <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Tax</th>
                                    <th>Settle Amount</th>
                                    <th>UTR No.</th>
                                    <th>Status</th>
                                        <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $item)
                                    <tr>
                                            <td class="fw-bold text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="transaction-id">{{ $item->transaction_id }}</span>
                                            </td>
                                            <td class="amount-{{ $item->status == 1 ? 'success' : ($item->status == 0 ? 'pending' : 'failed') }}">
                                                ₹{{ number_format($item->amount, 2) }}
                                            </td>
                                            <td class="text-muted">₹{{ number_format($item->tax, 2) }}</td>
                                            <td class="fw-bold">
                                                ₹{{ number_format($item->amount - $item->tax, 2) }}
                                            </td>
                                            <td>
                                                <span class="utr-number">{{ $item->data4 ?: $item->data1 ?: 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($item->status == 1)
                                                    <span class="status-badge success">
                                                        <i class="fas fa-check-circle"></i>Success
                                                    </span>
                                                @elseif($item->status == 0)
                                                    <span class="status-badge pending">
                                                        <i class="fas fa-clock"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="status-badge failed">
                                                        <i class="fas fa-times-circle"></i>Failed
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="date-time">
                                                    <i class="far fa-clock mr-1"></i>
                                                    {{ dformat($item->created_at, 'd-m-Y h:i:s') }}
                                                </span>
                                            </td>
                                    </tr>
                                @empty
                                    <tr>
                                            <td colspan="8">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox"></i>
                                                    <h5>No {{ $title }} Found</h5>
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
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            // Initialize Flatpickr for date inputs
            flatpickr("#from_date", {
                dateFormat: "Y-m-d",
                theme: "light"
            });
            flatpickr("#to_date", {
                dateFormat: "Y-m-d",
                theme: "light"
            });

            // Initialize DataTable with enhanced options (defensive init)
        if ($('#reportTable').length) {
                // Prevent double init across duplicate sections
                if (!window.__reportTableInit__) {
                    window.__reportTableInit__ = true;
                } else {
                    // Already initialized once in this page load
                    return;
                }
                // Destroy if already initialized (e.g., via partial reloads)
                if ($.fn.DataTable.isDataTable('#reportTable')) {
                    $('#reportTable').DataTable().clear().destroy();
                }
                var reportTable = $('#reportTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "ordering": true,
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "dom": 'l<"custom-info"f>rtip',
                    "pageLength": 25,
                    "language": {
                        "search": "Search transactions:",
                        "lengthMenu": "Show _MENU_ transactions per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ transactions",
                        "emptyTable": "No transactions found",
                        "zeroRecords": "No matching transactions found"
                    },
                    "order": [[0, "asc"]],
                    "columnDefs": [
                        { "orderable": false, "targets": [0] },
                        { "className": "text-center", "targets": [0] },
                        { "defaultContent": "", "targets": "_all" }
                    ]
                });
            }
        });
        
        // Copy table data to clipboard
        function copyTableData() {
            const table = document.getElementById('reportTable');
            const rows = table.querySelectorAll('tbody tr');
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Add headers
            const headers = ['#', 'Transaction ID', 'Amount', 'Tax', 'Settle Amount', 'UTR No.', 'Status', 'Date & Time'];
            csvContent += headers.join(',') + '\n';
            
            // Add data rows
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = [];
                cells.forEach((cell, index) => {
                    let text = cell.textContent.trim();
                    // Clean up the text (remove icons, extra spaces)
                    text = text.replace(/[^\w\s\-₹,.:]/g, '');
                    text = text.replace(/\s+/g, ' ');
                    rowData.push('"' + text + '"');
                });
                csvContent += rowData.join(',') + '\n';
            });
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "payin_ledger_data.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success message
            showNotification('Data copied to clipboard and downloaded as CSV!', 'success');
        }
        
        // Export to PDF
        function exportToPDF() {
            // Create a new window with the table content
            const printWindow = window.open('', '_blank');
            const table = document.getElementById('reportTable');
            
            // Create a copy of the table and sort it in ascending order for PDF
            const tableClone = table.cloneNode(true);
            const tbody = tableClone.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Sort rows by the date column (assuming it's the last column)
            rows.sort((a, b) => {
                const dateA = a.cells[a.cells.length - 1].textContent.trim();
                const dateB = b.cells[b.cells.length - 1].textContent.trim();
                return dateA.localeCompare(dateB);
            });
            
            // Clear tbody and append sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Payin Ledger Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .status-success { color: #28a745; }
                        .status-failed { color: #dc3545; }
                        .status-pending { color: #ffc107; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Payin Ledger Report</h1>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>
                    ${tableClone.outerHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            // Wait for content to load then print
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
        
        // Print table
        function printTable() {
            const printWindow = window.open('', '_blank');
            const table = document.getElementById('reportTable');
            
            // Create a copy of the table and sort it in ascending order for printing
            const tableClone = table.cloneNode(true);
            const tbody = tableClone.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Sort rows by the date column (assuming it's the last column)
            rows.sort((a, b) => {
                const dateA = a.cells[a.cells.length - 1].textContent.trim();
                const dateB = b.cells[b.cells.length - 1].textContent.trim();
                return dateA.localeCompare(dateB);
            });
            
            // Clear tbody and append sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Payin Ledger Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .status-success { color: #28a745; }
                        .status-failed { color: #dc3545; }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Payin Ledger Report</h1>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>
                    ${tableClone.outerHTML}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            // Wait for content to load then print
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
        
        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
                }
     </script>
@endsection
