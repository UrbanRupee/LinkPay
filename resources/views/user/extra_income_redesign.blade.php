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
            border-left: 4px solid var(--primary-red);
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
            border-left: 4px solid var(--primary-red);
            transition: transform 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
        }

        .summary-card.success { border-left-color: var(--primary-red); }
        .summary-card.pending { border-left-color: var(--light-red); }
        .summary-card.failed { border-left-color: var(--dark-red); }

        .summary-card .count {
            font-size: 2.5rem;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 0.5rem;
        }

        .summary-card .label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #FFFFFF;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            border: 2px solid var(--primary-red);
            background: var(--dark-gray);
            color: #FFFFFF;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .filter-btn:hover {
            background: var(--primary-red);
            color: #FFFFFF;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: var(--primary-red);
            color: #FFFFFF;
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
            border: 1px solid var(--primary-red);
            color: #FFFFFF;
            border-radius: 8px;
            padding: 0.6rem 1rem;
        }

        .filter-form .form-control:focus {
            background-color: var(--dark-gray);
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25);
            color: #FFFFFF;
        }

        .filter-form .form-control::placeholder {
            color: #9CA3AF;
        }

        .filter-form .btn-primary {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
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
            border-color: var(--primary-red);
        }

        /* Table Section */
        .table-panel {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            overflow: hidden;
        }

        .table-panel-header {
            background: var(--black);
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--primary-red);
        }

        .table-panel-header h6 {
            color: #FFFFFF;
            font-weight: 700;
            margin: 0;
            font-size: 1.1rem;
        }

        .table-panel-body {
            padding: 1.5rem;
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
            border: 1px solid var(--primary-red);
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
            border: 1px solid var(--primary-red);
            border-radius: 6px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--primary-red) !important;
            color: #FFFFFF !important;
            border-color: var(--primary-red);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-red) !important;
            color: #FFFFFF !important;
            border-color: var(--primary-red);
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
            color: var(--primary-red);
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
            background: rgba(220, 38, 38, 0.03);
        }

        .table tbody tr:hover {
            background: rgba(220, 38, 38, 0.1);
        }

        /* Status Badges */
        .badge-success {
            background: var(--primary-red);
            color: #FFFFFF;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-warning {
            background: var(--light-red);
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
            color: var(--primary-red);
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
    
    <!-- Page Header -->
    <div class="report-header">
        <h5><i class="fas fa-chart-line" style="margin-right: 0.75rem;"></i>{{ $title }}</h5>
    </div>

    <!-- Status Summary Cards -->
    <div class="status-summary-row">
        <div class="summary-card success">
            <div class="count">{{ $list->where('status', 1)->count() }}</div>
            <div class="label">Successful Transactions</div>
        </div>
        <div class="summary-card pending">
            <div class="count">{{ $list->where('status', 0)->count() }}</div>
            <div class="label">Pending Transactions</div>
        </div>
        <div class="summary-card failed">
            <div class="count">{{ $list->where('status', '!=', 1)->where('status', '!=', 0)->count() }}</div>
            <div class="label">Failed Transactions</div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
        <h6 class="filter-title"><i class="fas fa-filter" style="margin-right: 0.5rem;"></i>Filter by Status</h6>
        
        <div class="filter-buttons">
            <a href="{{ url()->current() }}" class="filter-btn {{ !request('status_filter') ? 'active' : '' }}">
                <i class="fas fa-list" style="margin-right: 0.5rem;"></i>All Transactions
            </a>
            <a href="{{ url()->current() }}?status_filter=success" class="filter-btn {{ request('status_filter') == 'success' ? 'active' : '' }}">
                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>Success Only
            </a>
            <a href="{{ url()->current() }}?status_filter=pending" class="filter-btn {{ request('status_filter') == 'pending' ? 'active' : '' }}">
                <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>Pending Only
            </a>
            <a href="{{ url()->current() }}?status_filter=failed" class="filter-btn {{ request('status_filter') == 'failed' ? 'active' : '' }}">
                <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>Failed Only
            </a>
        </div>

        <hr style="border-color: var(--medium-gray); margin: 1.5rem 0;">

        <h6 class="filter-title"><i class="fas fa-calendar-alt" style="margin-right: 0.5rem;"></i>Filter by Date & Search</h6>
        
        <form action="{{ url()->current() }}" method="GET" class="filter-form">
            @if(request('status_filter'))
                <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
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
                    <input type="text" class="form-control" id="search_query" name="search_query" 
                           value="{{ request('search_query') }}" placeholder="Transaction ID or UTR No.">
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
        <div class="table-panel-header">
            <h6><i class="fas fa-table" style="margin-right: 0.5rem;"></i>Payin Ledger Details</h6>
        </div>
        
        <div class="table-panel-body">
            <div class="table-responsive">
                <table class="table table-hover" id="payinReportTable">
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
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td><strong>{{ $item->transaction_id }}</strong></td>
                                <td>₹{{ number_format($item->amount, 2) }}</td>
                                <td>₹{{ number_format($item->tax, 2) }}</td>
                                <td><strong>₹{{ number_format($item->amount - $item->tax, 2) }}</strong></td>
                                <td>{{ $item->data4 ?: $item->data1 ?: 'N/A' }}</td>
                                <td>
                                    @if($item->status == 1)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle" style="margin-right: 0.3rem;"></i>Success
                                        </span>
                                    @elseif($item->status == 0)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock" style="margin-right: 0.3rem;"></i>Pending
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times-circle" style="margin-right: 0.3rem;"></i>Failed
                                        </span>
                                    @endif
                                </td>
                                <td>{{ dformat($item->created_at, 'd-m-Y h:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
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

</div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
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
                // Destroy existing if present
                if ($.fn.DataTable.isDataTable('#payinReportTable')) {
                    $('#payinReportTable').DataTable().destroy();
                }
                
                // Initialize fresh
                $('#payinReportTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "ordering": true,
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "pageLength": 25,
                    "order": [[7, "desc"]], // Sort by Date & Time descending
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
                        { "orderable": false, "targets": [0] }, // # column not sortable
                        { "className": "text-center", "targets": [0] } // Center align #
                    ],
                    "dom": 'Blfrtip', // Buttons, Length, Filter, Table, Info, Pagination
                    "buttons": [
                        {
                            extend: 'copy',
                            text: '<i class="fas fa-copy"></i> Copy',
                            className: 'btn btn-sm',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'csv',
                            text: '<i class="fas fa-file-csv"></i> CSV',
                            className: 'btn btn-sm',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            className: 'btn btn-sm',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            className: 'btn btn-sm',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print"></i> Print',
                            className: 'btn btn-sm',
                            exportOptions: { columns: ':visible' }
                        }
                    ]
                });
            }
            
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection







