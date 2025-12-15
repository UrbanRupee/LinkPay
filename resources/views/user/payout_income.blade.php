@extends('user.layout.NewUser')

@section('css')
    <style>
        /* RED & BLACK THEME */
        .payout-container {
            background: var(--black);
            padding: 1.5rem;
            min-height: 100vh;
        }
        
        .card-header {
            background: var(--black);
            color: #FFFFFF;
            border-bottom: 2px solid var(--primary-orange);
            padding: 1rem 1.5rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            font-size: 1.2rem;
            color: #FFFFFF;
        }
        
        /* Status cards */
        .status-summary {
            margin-bottom: 1.5rem;
        }
        .status-card {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        .status-card.success {
            border-left: 4px solid var(--primary-orange);
        }
        .status-card.failed {
            border-left: 4px solid var(--dark-red);
        }
        .status-card .count {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #FFFFFF;
        }
        .status-card .label {
            font-size: 0.9rem;
            color: #FFFFFF;
            font-weight: 600;
        }
        
        /* Filter buttons */
        .status-filters {
            margin-bottom: 1rem;
        }
        .status-filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            border: 2px solid var(--primary-orange);
            text-decoration: none;
            display: inline-block;
            background: var(--black);
            color: #FFFFFF;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        .status-filter-btn:hover {
            background: var(--dark-gray);
            color: #FFFFFF;
            text-decoration: none;
            transform: translateY(-2px);
            border-color: var(--light-orange);
        }
        .status-filter-btn.active {
            background: var(--black);
            color: var(--primary-orange);
            border-color: var(--primary-orange);
            border-width: 3px;
            font-weight: 700;
        }
        
        /* Table styles */
        .table-container {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            overflow: hidden;
        }
        .table-header-section {
            background: var(--black);
            padding: 1rem;
            border-bottom: 2px solid var(--primary-orange);
        }
        .table-header-section h6 {
            margin: 0;
            color: #FFFFFF;
            font-weight: 600;
        }
        
        .table thead th {
            background: var(--black);
            color: var(--primary-orange);
            font-weight: 600;
            border: none;
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        .table tbody td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--medium-gray);
            vertical-align: middle;
            color: #FFFFFF;
        }
        .table tbody tr:hover {
            background: rgba(241, 90, 34, 0.1);
        }
        .table tbody tr:nth-child(even) {
            background-color: rgba(241, 90, 34, 0.05);
        }
        
        /* Status badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-badge.success {
            background: var(--primary-orange);
            color: white;
        }
        .status-badge.failed {
            background: var(--dark-red);
            color: white;
        }
        .status-badge.pending {
            background: var(--light-orange);
            color: var(--black);
        }
        
        /* Amount styling */
        .amount-success {
            color: var(--primary-orange);
            font-weight: 600;
        }
        .amount-failed {
            color: var(--dark-red);
            font-weight: 600;
        }
        
        /* Form styles */
        .filter-section {
            background: var(--dark-gray);
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .filter-section h6 {
            color: #FFFFFF;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .filter-form label {
            color: #FFFFFF;
            font-weight: 500;
        }
        .filter-form .form-control {
            border: 1px solid var(--primary-orange);
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            background: var(--medium-gray);
            color: #FFFFFF;
        }
        .filter-form .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.2rem rgba(241, 90, 34, 0.25);
            background: var(--dark-gray);
            color: #FFFFFF;
        }
        .filter-form .form-control::placeholder {
            color: #9CA3AF;
        }
        .filter-form .btn {
            border-radius: 4px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        /* Export buttons */
        .export-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .export-btn {
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-flex;
            background: var(--dark-gray);
            border: 1px solid var(--primary-orange);
            color: var(--primary-orange);
            align-items: center;
            gap: 0.25rem;
        }
        .export-btn:hover {
            background: var(--primary-orange);
            color: white;
            text-decoration: none;
        }
        
        /* DataTable customization */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--primary-orange);
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            background: var(--medium-gray);
            color: var(--primary-text-color);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 4px;
            margin: 0 0.1rem;
            border: 1px solid var(--primary-orange);
            background: var(--dark-gray);
            color: var(--primary-text-color) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-orange) !important;
            color: white !important;
            border-color: var(--primary-orange);
        }
        
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: var(--primary-text-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .status-summary .row > div {
                margin-bottom: 1rem;
            }
            .filter-form .col-md-3 {
                margin-bottom: 1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="payout-container">
    <div class="row">
        <div class="col-sm-12">

            <div class="box">
                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5><i class="fas fa-money-bill-wave mr-2"></i>{{ $title }}</h5>
                    <button class="btn btn-light btn-sm d-none" id="exportDataBtn">
                        <i class="fas fa-download mr-1"></i>Export Data
                    </button>
                </div>
                
                <div class="card-body p-4">
                    <!-- Status Summary Cards -->
                    <div class="status-summary">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="status-card success">
                                    <div class="count text-success">{{ $list->where('status', 1)->count() }}</div>
                                    <div class="label text-success">Successful Payouts</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="status-card failed">
                                    <div class="count text-danger">{{ $list->where('status', '!=', 1)->where('status', '!=', 0)->count() }}</div>
                                    <div class="label text-danger">Failed Payouts</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter Buttons -->
                    <div class="status-filters">
                        <h6 class="mb-3"><i class="fas fa-filter mr-2"></i>Filter by Status:</h6>
                        <a href="{{ url()->current() }}" class="status-filter-btn all {{ !request('status_filter') ? 'active' : '' }}">
                            <i class="fas fa-list mr-1"></i>All Payouts
                        </a>
                        <a href="{{ url()->current() }}?status_filter=success" class="status-filter-btn success {{ request('status_filter') == 'success' ? 'active' : '' }}">
                            <i class="fas fa-check-circle mr-1"></i>Success Only
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
                            <div class="col-md-3">

                                    <label for="from_date" class="form-label fw-bold">From Date:</label>
                                <input type="text" class="form-control flatpickr-input" id="from_date" name="from_date" value="{{ request('from_date') }}" placeholder="Select From Date">
                            </div>
                            <div class="col-md-3">

                                    <label for="to_date" class="form-label fw-bold">To Date:</label>
                                <input type="text" class="form-control flatpickr-input" id="to_date" name="to_date" value="{{ request('to_date') }}" placeholder="Select To Date">
                            </div>
                            <div class="col-md-3">

                                    <label for="search" class="form-label fw-bold">Search:</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Transaction ID or UTR No.">
                            </div>

                                <div class="col-md-3 d-flex justify-content-end">
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
                            <h6><i class="fas fa-table mr-2"></i>Payout Report Details</h6>
                        </div>
                        <div class="export-buttons">
                            <a href="#" class="export-btn copy" onclick="copyTableData()">
                                <i class="fas fa-copy"></i> Copy
                            </a>
                            <a href="{{ route('user.export.payout_report') }}?{{ http_build_query(request()->all()) }}" class="export-btn csv">
                                <i class="fas fa-file-csv"></i> CSV
                            </a>
                            <a href="{{ route('user.export.payout_report') }}?{{ http_build_query(request()->all()) }}" class="export-btn excel">
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

                            <table class="table table-hover dataTable" id="payoutReportTable">
                            <thead>
                                <tr>
                                    <th>#</th>

                                        <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Surcharge</th>
                                    <th>Total Deduction</th>
                                    <th>Name</th>
                                    <th>Account No.</th>
                                    <th>IFSC Code</th>
                                    <th>Mode</th>
                                    <th>UTR No.</th>
                                    <th>Status</th>
                                    <th>Remark</th>

                                        <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $item)
                                    <tr>
                                            <td class="fw-bold text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->transaction_id }}</td>
                                            <td class="amount-{{ $item->status == 1 ? 'success' : 'failed' }}">
                                                ₹{{ number_format($item->amount ?? 0, 2) }}
                                            </td>
                                            <td>₹{{ number_format($item->tax ?? 0, 2) }}</td>
                                            <td class="fw-bold">
                                                ₹{{ number_format(($item->amount ?? 0) + ($item->tax ?? 0), 2) }}
                                            </td>
                                            <td>{{ $item->holder_name ?? 'N/A' }}</td>
                                            <td>{{ $item->account_no ?? 'N/A' }}</td>
                                            <td>{{ $item->ifsc_code ?? 'N/A' }}</td>
                                            <td>{{ $item->mode ?? 'N/A' }}</td>
                                            <td>{{ $item->utr ?? 'N/A' }}</td>
                                            <td>
                                                @if($item->status == 0)
                                                    <span class="status-badge pending">Pending</span>
                                                @elseif($item->status == 1)
                                                    <span class="status-badge success">Success</span>
                                                @else
                                                    <span class="status-badge failed">Failed</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->remark ?? 'N/A' }}</td>
                                            <td>{{ $item->created_at ? dformat($item->created_at, 'd-m-Y h:i:s') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>

                                            <td colspan="13">
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

            // Initialize DataTable with enhanced options
            if ($('#payoutReportTable').length) {
                var payoutTable = $('#payoutReportTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "ordering": true,
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "dom": 'l<"custom-info"f>rtip',
                    "pageLength": 25,
                    "language": {
                        "search": "Search payouts:",
                        "lengthMenu": "Show _MENU_ payouts per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ payouts",
                        "emptyTable": "No payouts found",
                        "zeroRecords": "No matching payouts found"
                    },
                    "order": [[13, "desc"]],
                    "columnDefs": [
                        { "orderable": false, "targets": [0] }, // Disable sorting for serial number
                        { "className": "text-center", "targets": [0] }, // Center align serial number
                        { "defaultContent": "", "targets": "_all" }, // Set default content for missing columns
                        { "width": "5%", "targets": 0 }, // Adjust width for # column
                        { "width": "10%", "targets": 1 }, // Adjust width for Transaction ID
                        { "width": "8%", "targets": [2, 3, 4] }, // Adjust width for amount columns
                        { "width": "12%", "targets": [5, 6, 7, 8] }, // Adjust width for account details
                        { "width": "10%", "targets": 9 }, // Adjust width for UTR
                        { "width": "8%", "targets": 10 }, // Adjust width for status
                        { "width": "15%", "targets": [11, 12] } // Adjust width for remark and date
                    ]
                });

                // Export button functionality for the header button
                $('#exportDataBtn').on('click', function() {
                    window.location.href = "{{ route('user.export.payout_report') }}?{{ http_build_query(request()->all()) }}";
                });
            }
        });
        
        // Copy table data to clipboard
        function copyTableData() {
            const table = document.getElementById('payoutReportTable');
            const rows = table.querySelectorAll('tbody tr');
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Add headers
            const headers = ['#', 'Transaction ID', 'Amount', 'Tax', 'Settle Amount', 'Account Details', 'UTR No.', 'Status', 'Remark', 'Date & Time'];
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
            link.setAttribute("download", "payout_report_data.csv");
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
            const table = document.getElementById('payoutReportTable');
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Payout Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; font-weight: bold; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .status-success { color: #28a745; }
                        .status-failed { color: #dc3545; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Payout Report</h1>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>
                    ${table.outerHTML}
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
            const table = document.getElementById('payoutReportTable');
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>Payout Report</title>
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
                        <h1>Payout Report</h1>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
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