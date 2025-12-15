@extends('user.layout.NewUser') {{-- Crucial: Extends the new layout --}}

@section('css')
    <style>
        /* RED & BLACK THEME - Settlement Report */
        .card-header {
            background-color: var(--black);
            border-bottom: 2px solid var(--primary-orange);
            padding: 1.25rem 1.5rem;
        }
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary-text-color);
        }

        /* General table styles for responsiveness and appearance */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            margin-bottom: 1rem;
        }

        .table {
            width: 100% !important;
            min-width: 700px;
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--black);
            color: var(--primary-orange);
            font-weight: 600;
            border-bottom: 2px solid var(--medium-gray);
            vertical-align: middle;
            padding: 0.85rem 1.25rem;
            white-space: nowrap;
            text-align: left;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 0.75rem 1.25rem;
            white-space: nowrap;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--primary-text-color);
            border-bottom: 1px solid var(--medium-gray);
        }

        .table tbody tr:hover {
            background: rgba(241, 90, 34, 0.1);
        }

        .table tbody tr:nth-child(even) {
            background-color: rgba(241, 90, 34, 0.05);
        }

        /* Column width adjustments for this specific table */
        .table th.col-id, .table td.col-id { width: 50px; min-width: 50px; text-align: center; }
        .table th.col-utr, .table td.col-utr { width: 150px; min-width: 150px; }
        .table th.col-amount, .table td.col-amount { width: 150px; min-width: 150px; text-align: right; }
        .table th.col-tax, .table td.col-tax { width: 120px; min-width: 120px; text-align: right; }
        .table th.col-settled, .table td.col-settled { width: 150px; min-width: 150px; text-align: right; }
        .table th.col-status, .table td.col-status { width: 100px; min-width: 100px; text-align: center; }
        .table th.col-datetime, .table td.col-datetime { width: 170px; min-width: 170px; }
        .table th.col-bank, .table td.col-bank { width: 200px; min-width: 200px; }
        .table th.col-actions, .table td.col-actions { width: 100px; min-width: 100px; text-align: center; }

        /* Styles for badges */
        .badge {
            padding: 0.4em 0.6em;
            font-size: 80%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            border-radius: 0.25rem;
        }
        .badge-success { background-color: var(--primary-orange); color: #fff; }
        .badge-danger { background-color: var(--dark-red); color: #fff; }
        .badge-warning { background-color: var(--light-orange); color: var(--black); }

        /* Styling for the filter form */
        .filter-form .form-control {
            border-radius: var(--border-radius-md);
        }
        .filter-form .btn {
            border-radius: var(--border-radius-md);
        }

        /* DataTables specific adjustments for layout */
        .dataTables_filter, .dt-buttons {
            display: none !important;
        }
        .dt-custom-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .dt-custom-controls .dataTables_length {
            margin-bottom: 0.5rem;
            flex-shrink: 0;
        }
        .dt-custom-controls .custom-search-input {
            flex-grow: 1;
            margin-left: 1rem;
            max-width: 300px;
            margin-bottom: 0.5rem;
        }
        .dt-custom-controls .custom-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }
        .dt-custom-controls .btn {
            white-space: nowrap;
        }

        /* Pagination & Info styling */
        .dataTables_info, .dataTables_paginate {
            padding-top: 1rem;
            padding-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .dataTables_info {
            flex-grow: 1;
            margin-bottom: 0.5rem;
        }
        .dataTables_paginate {
            margin-left: auto;
            margin-bottom: 0.5rem;
        }
        .dataTables_paginate .pagination {
            margin-bottom: 0;
        }

        /* Summary cards */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .summary-card {
            border-radius: var(--border-radius-lg);
            padding: 1rem 1.25rem;
            background: #111;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 10px 20px rgba(0,0,0,0.35);
        }
        .summary-card h6 {
            margin: 0;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.75);
        }
        .summary-card .amount {
            margin-top: 0.35rem;
            font-size: 1.6rem;
            font-weight: 700;
        }
        .summary-card.total { background: linear-gradient(135deg, #f15a22, #f8663d); }
        .summary-card.tax { background: linear-gradient(135deg, #ffb347, #ffcc33); color: #222; }
        .summary-card.net { background: linear-gradient(135deg, #1d976c, #93f9b9); color: #0b2a20; }

        @media (max-width: 767.98px) {
            .dt-custom-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            .dt-custom-controls .custom-search-input {
                margin-left: 0;
                width: 100%;
                max-width: none;
            }
            .dt-custom-controls .custom-buttons {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            .dataTables_info, .dataTables_paginate {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    {{-- Flatpickr CSS for date pickers --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="box"> {{-- Using the .box style from the new layout --}}
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{{ $title }}</h5>
                    <a href="{{ $exportUrl }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-export me-1"></i> Export CSV
                    </a>
                </div>
                <div class="card-body"> {{-- Using .card-body for Bootstrap 4 consistency --}}

                    <div class="summary-row">
                        <div class="summary-card total">
                            <h6>Total Amount</h6>
                            <div class="amount">₹{{ number_format($totals['gross'] ?? 0, 2) }}</div>
                        </div>
                        <div class="summary-card tax">
                            <h6>Service Charge</h6>
                            <div class="amount">₹{{ number_format($totals['tax'] ?? 0, 2) }}</div>
                        </div>
                        <div class="summary-card net">
                            <h6>Settled Amount</h6>
                            <div class="amount">₹{{ number_format($totals['settled'] ?? 0, 2) }}</div>
                        </div>
                        @if(isset($totalHold) && $totalHold > 0)
                        <div class="summary-card" style="background: linear-gradient(135deg, #d4a574, #e6c9a0); color: #333;">
                            <h6>Total Hold Amount</h6>
                            <div class="amount">₹{{ number_format($totalHold, 2) }}</div>
                        </div>
                        @endif
                    </div>

                    {{-- Filter Form (Date & Search) --}}
                    <form action="{{ url()->current() }}" method="GET" class="mb-4 filter-form">
                        <div class="row g-3 align-items-end"> {{-- Bootstrap grid --}}
                            <div class="col-md-4 col-sm-6">
                                <label for="from_date" class="form-label">From Date:</label>
                                <input type="text" class="form-control flatpickr-input" id="from_date" name="from_date" value="{{ request('from_date') }}" placeholder="Select From Date">
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label for="to_date" class="form-label">To Date:</label>
                                <input type="text" class="form-control flatpickr-input" id="to_date" name="to_date" value="{{ request('to_date') }}" placeholder="Select To Date">
                            </div>
                            <div class="col-md-4 col-sm-6 d-flex justify-content-end align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Apply Filters</button>
                                <a href="{{ url()->current() }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>

                    {{-- DataTables Controls (Length menu, Search, Buttons) --}}
                    <div class="dt-custom-controls mb-3">
                        <div class="dataTables_length" id="settlementReportTable_length"></div>
                        <div class="custom-search-wrapper flex-grow-1 ml-md-3 mt-2 mt-md-0">
                            <input type="search" class="form-control custom-search-input" placeholder="Search current table data...">
                        </div>
                        <div class="dt-buttons custom-buttons mt-2 mt-md-0" id="settlementReportTable_buttons"></div>
                    </div>

                    {{-- The actual table wrapped in table-responsive --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="settlementReportTable">
                            <thead>
                                <tr>
                                    <th class="col-id">#</th>
                                    <th class="col-utr">UTR No.</th>
                                    <th class="col-amount">Total Amount</th>
                                    <th class="col-tax">Service Charge</th>
                                    <th class="col-settled">Settled Amount</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-datetime">Credited at</th>
                                    <th class="col-bank">Bank Details</th>
                                    <th class="col-description">Description</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($list as $item)
                                    @php
                                        // amount = settled amount (after hold), data3 = gross amount (without tax), data4 = tax, data5 = hold amount
                                        $settledAmount = (float) ($item->amount ?? 0); // Amount paid to payout (net - hold)
                                        $taxAmount = (float) ($item->data4 ?? 0);
                                        $holdAmount = (float) ($item->data5 ?? 0);
                                        $grossAmountWithoutTax = (float) ($item->data3 ?? 0);
                                        // If data3 is not set, calculate from settled + tax (for backward compatibility)
                                        if ($grossAmountWithoutTax == 0) {
                                            $grossAmountWithoutTax = $settledAmount + $taxAmount + $holdAmount;
                                        }
                                        $totalGrossAmount = $grossAmountWithoutTax + $taxAmount; // Total gross including tax
                                        $bankName = bank($item->userid, 'bank_name') ?: '—';
                                        $accountNo = bank($item->userid, 'account_no') ?: '—';
                                        $ifscCode = bank($item->userid, 'ifsc_code') ?: '—';
                                    @endphp
                                    <tr>
                                        <td class="col-id">{{ $loop->iteration }}</td>
                                        <td class="col-utr">{{ $item->data2 }}</td> {{-- Assuming data2 is UTR No. --}}
                                        <td class="col-amount">₹{{ number_format($totalGrossAmount, 2) }}</td>
                                        <td class="col-tax">₹{{ number_format($taxAmount, 2) }}</td>
                                        <td class="col-settled">₹{{ number_format($settledAmount, 2) }}</td>
                                        <td class="col-status">{!! $item->status == 1 ? "<span class='badge badge-success'>Success</span>" : ($item->status == 0 ? "<span class='badge badge-warning text-dark'>Pending</span>" : "<span class='badge badge-danger'>Fail</span>") !!}</td>
                                        <td class="col-datetime">{{ dformat($item->created_at, 'd-m-Y h:i:s') }}</td>
                                        <td class="col-bank">
                                            @if($bankName != '—' || $accountNo != '—' || $ifscCode != '—')
                                                <div style="font-size: 0.85rem;">
                                                    @if($bankName != '—')
                                                        <div><strong>Bank:</strong> {{ $bankName }}</div>
                                                    @endif
                                                    @if($accountNo != '—')
                                                        <div><strong>A/C:</strong> {{ $accountNo }}</div>
                                                    @endif
                                                    @if($ifscCode != '—')
                                                        <div><strong>IFSC:</strong> {{ $ifscCode }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="col-description">{{ $item->data1 ?? '—' }}</td>
                                        <td class="col-actions">
                                            @if($item->status == 1)
                                                <a href="{{ route('user.settlement.invoice', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Download Invoice">
                                                    <i class="fas fa-file-invoice"></i> Invoice
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No {{ $title }} found!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Holding Report Section --}}
                    @if(isset($holdList) && count($holdList) > 0)
                    <div class="mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h5 class="mb-0">Hold Amount Report</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="holdReportTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>UTR No.</th>
                                        <th>Gross Amount</th>
                                        <th>Service Charge</th>
                                        <th>Net Amount</th>
                                        <th>Hold Amount</th>
                                        <th>Settled Amount</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($holdList as $item)
                                        @php
                                            $settledAmount = (float) ($item->amount ?? 0);
                                            $taxAmount = (float) ($item->data4 ?? 0);
                                            $holdAmount = (float) ($item->data5 ?? 0);
                                            $grossAmountWithoutTax = (float) ($item->data3 ?? 0);
                                            if ($grossAmountWithoutTax == 0) {
                                                $grossAmountWithoutTax = $settledAmount + $taxAmount + $holdAmount;
                                            }
                                            $totalGrossAmount = $grossAmountWithoutTax + $taxAmount;
                                            $netAmount = $grossAmountWithoutTax - $taxAmount;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->data2 ?? '—' }}</td>
                                            <td>₹{{ number_format($totalGrossAmount, 2) }}</td>
                                            <td>₹{{ number_format($taxAmount, 2) }}</td>
                                            <td>₹{{ number_format($netAmount, 2) }}</td>
                                            <td><strong>₹{{ number_format($holdAmount, 2) }}</strong></td>
                                            <td>₹{{ number_format($settledAmount, 2) }}</td>
                                            <td>{{ dformat($item->created_at, 'd-m-Y h:i:s') }}</td>
                                            <td>{{ $item->data1 ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-info fw-bold">
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>₹{{ number_format($holdList->sum(function($item) { 
                                            $gross = (float)($item->data3 ?? 0);
                                            $tax = (float)($item->data4 ?? 0);
                                            if ($gross == 0) {
                                                $gross = (float)($item->amount ?? 0) + $tax + (float)($item->data5 ?? 0);
                                            }
                                            return $gross - $tax;
                                        }), 2) }}</strong></td>
                                        <td><strong>₹{{ number_format($totalHold, 2) }}</strong></td>
                                        <td><strong>₹{{ number_format($holdList->sum('amount'), 2) }}</strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            // Initialize Flatpickr for date inputs
            flatpickr("#from_date", {
                dateFormat: "Y-m-d", //YYYY-MM-DD format
            });
            flatpickr("#to_date", {
                dateFormat: "Y-m-d",
            });

            // Initialize DataTable for the settlement report table
            if ($('#settlementReportTable').length) {
                var settlementTable = $('#settlementReportTable').DataTable({
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "ordering": true,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "dom": '<"dt-custom-controls"l<"custom-search-wrapper"f>B>rtip', // CUSTOM DOM layout
                    "buttons": [
                        { extend: 'copy', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-secondary btn-sm' },
                        { extend: 'csv', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-secondary btn-sm' },
                        { extend: 'excel', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-success btn-sm' },
                        { extend: 'pdf', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-danger btn-sm' },
                        { extend: 'print', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-info btn-sm' }
                    ],
                    "columnDefs": [
                        { "orderable": false, "targets": [0, 7, 8, 9] },
                        { "defaultContent": "", "targets": "_all" },
                    ],
                    "initComplete": function() {
                        // Move and style DataTables' generated elements
                        // 1. Move and style the search input
                        $('.dataTables_filter input')
                            .addClass('form-control custom-search-input')
                            .attr('placeholder', 'Search current table data...');
                        $('#settlementReportTable_filter').appendTo('.dt-custom-controls .custom-search-wrapper');

                        // 2. Move the length menu
                        $('#settlementReportTable_length').appendTo('.dt-custom-controls');

                        // 3. Move the buttons
                        $('#settlementReportTable_buttons').appendTo('.dt-custom-controls');
                        
                        // 4. Ensure DataTables' buttons get proper Bootstrap styling
                        $('#settlementReportTable_buttons .dt-button').removeClass('dt-button');
                    }
                });

                // Initialize DataTable for hold report table if it exists
                if ($('#holdReportTable').length) {
                    $('#holdReportTable').DataTable({
                        "paging": true,
                        "searching": true,
                        "info": true,
                        "ordering": true,
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        "dom": 'lBfrtip',
                        "buttons": [
                            { extend: 'copy', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-secondary btn-sm' },
                            { extend: 'csv', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-secondary btn-sm' },
                            { extend: 'excel', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-success btn-sm' },
                            { extend: 'pdf', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-danger btn-sm' },
                            { extend: 'print', exportOptions: { columns: ':visible' }, className: 'btn btn-outline-info btn-sm' }
                        ],
                    });
                }
            });
        });
    </script>
@endsection