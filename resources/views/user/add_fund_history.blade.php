@extends('user.layout.NewUser')

@section('css')
    <style>
        /* RED & BLACK THEME */
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
        .table thead th {
            background-color: var(--black);
            color: var(--primary-orange);
            font-weight: 600;
            border-bottom: 2px solid var(--medium-gray);
            vertical-align: middle;
            padding: 0.75rem 1.25rem;
        }
        .table tbody td {
            vertical-align: middle;
            padding: 0.75rem 1.25rem;
            color: var(--primary-text-color);
        }
        .table tbody tr:hover {
            background: rgba(241, 90, 34, 0.1);
        }
        .table tbody tr:nth-child(even) {
            background-color: rgba(241, 90, 34, 0.05);
        }
        /* Red & Black badge colors */
        .badge-success { background-color: var(--primary-orange); color: #fff; }
        .badge-danger { background-color: var(--dark-red); color: #fff; }
        .badge-warning { background-color: var(--light-orange); color: var(--black); }
    </style>
@endsection

@section('content')
<div class="container-fluid"> {{-- Use container-fluid for full width, or container for fixed width --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="box"> {{-- Replaced .card with .box to match new layout styles --}}
                <div class="card-header">
                    <h5>{{$title}}</h5>
                </div>
                <div class="card-body"> {{-- Replaced .card-block with .card-body for Bootstrap 4 consistency --}}
                    <div class="table-responsive"> {{-- Removed scroll-container class, let Bootstrap handle responsiveness --}}
                        <table class="table table-striped table-hover dataTable" id="transactionTable"> {{-- Added dataTable class for JS targeting --}}
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Amount</th>
                                    <th>To/From</th> {{-- Changed 'To' to 'To/From' for broader use --}}
                                    <th>Mode/Type</th> {{-- Changed 'Mode' to 'Mode/Type' --}}
                                    <th>Remarks</th>
                                    <th>Datetime</th>
                                    <th>Status</th> {{-- Un-commented Status column --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transaction as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if($item->category == 'admin_deduction' && $item->type == 'debit')
                                                <span class="text-danger">-{{ balance($item->amount, '₹') }}</span>
                                            @else
                                                <span class="text-success">+{{ balance($item->amount, '₹') }}</span>
                                            @endif
                                        </td>
                                        {{-- 'To/From' column logic --}}
                                        <td>
                                            @if($item->category == 'settlement')
                                                {{ $item->data2 ?? 'Settlement' }} {{-- UTR for settlement --}}
                                            @elseif($item->category == 'add_fund' && strpos($item->data1 ?? '', 'Settlement Hold') !== false)
                                                {{ $item->data2 ?? 'Hold' }} {{-- UTR for hold amount --}}
                                            @elseif($item->category == 'admin_deduction')
                                                ADMIN {{-- Admin deduction --}}
                                            @elseif(isset($item->data3) && $item->data3 != 'Hold')
                                                {{ strtoupper($item->data3) }}
                                            @elseif(isset($item->receiver_details))
                                                {{ $item->receiver_details ?? 'N/A' }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        {{-- 'Mode/Type' column logic --}}
                                        <td>
                                            @if($item->category == 'settlement')
                                                SETTLEMENT
                                            @elseif($item->category == 'add_fund' && strpos($item->data1 ?? '', 'Settlement Hold') !== false)
                                                HOLD AMOUNT
                                            @elseif($item->category == 'admin_deduction')
                                                ADMIN {{ strtoupper($item->type ?? 'DEDUCTION') }}
                                            @elseif($item->category == 'add_fund' && strpos($item->data1 ?? '', 'Admin') !== false)
                                                ADMIN {{ strtoupper($item->type ?? 'CREDIT') }}
                                            @elseif(isset($item->type) && !is_object($item->type))
                                                {{ strtoupper($item->type) }}
                                            @elseif(isset($item->payment_method))
                                                {{ strtoupper($item->payment_method) }}
                                            @else
                                                {{ strtoupper($item->category ?? 'N/A') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->category == 'settlement')
                                                Settlement - UTR: {{ $item->data2 ?? 'N/A' }}
                                            @elseif($item->category == 'admin_deduction')
                                                {{ $item->data1 ?? 'Admin Deduction' }} - Wallet: {{ strtoupper($item->data2 ?? 'N/A') }}
                                            @elseif($item->category == 'add_fund')
                                                {{ $item->data1 ?? '—' }}
                                            @else
                                                {{ $item->data1 ?? '—' }}
                                            @endif
                                        </td>
                                        <td>{{ dformat($item->created_at,'d-m-Y h:i A') }}</td>
                                        <td>
                                            {{-- Assuming status field is consistent (0=pending, 1=success, 2=failed) --}}
                                            @if($item->status == 1)
                                                <span class="badge badge-success">Success</span>
                                            @elseif($item->status == 2)
                                                <span class="badge badge-danger">Declined</span>
                                            @else
                                                <span class="badge badge-warning text-dark">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No {{ $title }} Report found!</td> {{-- Adjusted colspan --}}
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
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Check if the table exists before initializing DataTable
            if ($('#transactionTable').length) {
                initDataTable('#transactionTable', {
                    // DataTables options specific for this table
                    "paging": true,
                    "searching": true,
                    "info": true,
                    "ordering": true,
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], // Show rows per page option
                    "dom": 'lBfrtip', // Layout with length menu, buttons, filter, etc.
                    "buttons": ['copy', 'csv', 'excel', 'pdf', 'print'],
                    "columnDefs": [
                        { "orderable": false, "targets": [0] }, // Disable sorting for serial number
                        { "defaultContent": "", "targets": "_all" } // Set default content for missing columns
                    ],
                    "language": {
                        "emptyTable": "No transactions found",
                        "zeroRecords": "No matching transactions found"
                    }
                });
            }
            
            // Your existing JS for form/usercheck, ensure it's still needed on this page.
            // If not, remove it.
            // formasync('edit_profile'); // This looks like it belongs to an edit profile page
            // $("#userid").on('change', function() { /* ... */ }); // This also looks like it belongs elsewhere
            // $("#edit_profile").validate({ /* ... */ }); // This too
        });
    </script>
@endsection