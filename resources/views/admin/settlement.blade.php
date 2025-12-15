@extends('admin.layout.user')
@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .summary-card .card-title {
        color: white;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .summary-card .card-value {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
    }
    .amount-display {
        font-weight: bold;
        color: #2c3e50;
    }
    .tax-amount {
        color: #e74c3c;
        font-size: 0.9em;
    }
    .net-amount {
        color: #27ae60;
        font-size: 1.1em;
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
        
        <!-- Date Selection Form -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Settlement Date Range
                        </h6>
                        <form method="GET" action="{{ url('/admin/settlement') }}" id="dateFilterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="text" class="form-control" id="from_date" name="from_date" 
                                           value="{{ request('from_date', $lastDate) }}" placeholder="Select start date" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="text" class="form-control" id="to_date" name="to_date" 
                                           value="{{ request('to_date', $sub1todayDate) }}" placeholder="Select end date" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i>
                                        Filter Settlement
                                    </button>
                                    <a href="{{ url('/admin/settlement') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-refresh me-1"></i>
                                        Reset
                                    </a>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Current Range: {{ $lastDate }} to {{ $sub1todayDate }}
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <h6 class="card-title">Total Gross Amount</h6>
                    <p class="card-value">₹{{ number_format($total1 ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <h6 class="card-title">Total Service Charge</h6>
                    <p class="card-value">₹{{ number_format($total2 ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <h6 class="card-title">Total Net Amount</h6>
                    <p class="card-value">₹{{ number_format($total3 ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="card-title mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            {{$title}} Details
                        </h6>
                            <a href="{{ route('admin.export.settlements', ['from_date' => $lastDate, 'to_date' => $sub1todayDate]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-file-export me-1"></i>Export CSV
                            </a>
                        </div>
                        <p class="text-muted">
                            Settlement period: <strong>{{$lastDate}}</strong> to <strong>{{$sub1todayDate}}</strong>
                        </p>
                        <div class="table-responsive">
                            <table class="table table-hover" id="settlementTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>User Details</th>
                                        <th class="text-end">Gross Amount</th>
                                        <th class="text-end">Service Charge</th>
                                        <th class="text-end">Net Amount</th>
                                        <th>Bank Details</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $total1 = 0;
                                    $total2 = 0;
                                    $total3 = 0;
                                    @endphp
                                    @if (count($data) > 0)
                                        @foreach ($data as $item)
                                            @php
                                                $bankDetails = \App\Models\User_Bank::where('userid', $item->userid)->first();
                                                $bankName = $bankDetails ? ($bankDetails->bank_name ?? '—') : '—';
                                                $accountNo = $bankDetails ? ($bankDetails->account_no ?? '—') : '—';
                                                $ifscCode = $bankDetails ? ($bankDetails->ifsc_code ?? '—') : '—';
                                            @endphp
                                            <tr id="table{{ $item->id }}" class="align-middle">
                                                <td class="fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div>
                                                        <strong>{{$item->userid}}</strong>
                                                        <br>
                                                        <small class="text-muted">{{$item->name}}</small>
                                                    </div>
                                                </td>
                                                <td class="text-end amount-display">
                                                    ₹{{ number_format($item->BTotal, 2) }}
                                                </td>
                                                <td class="text-end tax-amount">
                                                    ₹{{ number_format($item->TaxTotal, 2) }}
                                                </td>
                                                <td class="text-end net-amount">
                                                    ₹{{ number_format($item->ATotal, 2) }}
                                                </td>
                                                <td>
                                                    @if($bankName != '—' || $accountNo != '—' || $ifscCode != '—')
                                                        <small>
                                                            @if($bankName != '—') <strong>Bank:</strong> {{ $bankName }}<br> @endif
                                                            @if($accountNo != '—') <strong>A/C:</strong> {{ $accountNo }}<br> @endif
                                                            @if($ifscCode != '—') <strong>IFSC:</strong> {{ $ifscCode }} @endif
                                                        </small>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-success btn-sm"
                                                        onclick="approve('utr','{{ $item->userid }}','{{$item->ATotal}}','{{$item->BTotal}}','{{$item->TaxTotal}}','{{$lastDate}}','{{$sub1todayDate}}')"
                                                        title="Approve Settlement">
                                                        <i class="fas fa-check me-1"></i>
                                                        Approve
                                                    </button>
                                                </td>
                                            </tr>
                                            @php
                                            $total1 += $item->BTotal;
                                            $total2 += $item->TaxTotal;
                                            $total3 += $item->ATotal;
                                            @endphp
                                        @endforeach
                                        <!-- Total Row -->
                                        <tr class="table-info fw-bold">
                                            <td colspan="2" class="text-end">
                                                <strong>GRAND TOTAL:</strong>
                                            </td>
                                            <td class="text-end amount-display">
                                                ₹{{ number_format($total1, 2) }}
                                            </td>
                                            <td class="text-end tax-amount">
                                                ₹{{ number_format($total2, 2) }}
                                            </td>
                                            <td class="text-end net-amount">
                                                ₹{{ number_format($total3, 2) }}
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <br>
                                                    No {{$title}} found for the selected date range!
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(document).ready(function() {
        // Initialize Flatpickr for date inputs
        flatpickr("#from_date", {
            dateFormat: "Y-m-d H:i:s",
            enableTime: true,
            time_24hr: true,
            placeholder: "Select start date and time"
        });
        
        flatpickr("#to_date", {
            dateFormat: "Y-m-d H:i:s",
            enableTime: true,
            time_24hr: true,
            placeholder: "Select end date and time"
        });

        // Initialize DataTable for better table functionality
        if ($('#settlementTable').length) {
            $('#settlementTable').DataTable({
                "paging": true,
                "searching": true,
                "info": true,
                "ordering": true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }, // Serial number and Action columns
                    { "className": "text-end", "targets": [2, 3, 4] } // Amount columns
                ],
                "language": {
                    "search": "Search settlements:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ settlements",
                    "infoEmpty": "No settlements found",
                    "infoFiltered": "(filtered from _MAX_ total settlements)"
                }
            });
        }

        // Auto-submit form when dates change
        $('#from_date, #to_date').on('change', function() {
            // Optional: Auto-submit when both dates are selected
            if ($('#from_date').val() && $('#to_date').val()) {
                // Uncomment the line below to enable auto-submit
                // $('#dateFilterForm').submit();
            }
        });
    });

    function approve(utr, userid, amount, bamount, tax, startdate, enddate) {
        // Create modal for hold amount input
        const modalHtml = `
            <div class="modal fade" id="settlementModal" tabindex="-1" aria-labelledby="settlementModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="settlementModalLabel">Confirm Settlement Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                <p><strong>User:</strong> ${userid}</p>
                <p><strong>Gross Amount:</strong> ₹${parseFloat(bamount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</p>
                                <p><strong>Service Charge:</strong> ₹${parseFloat(tax).toLocaleString('en-IN', {minimumFractionDigits: 2})}</p>
                <p><strong>Net Amount:</strong> ₹${parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</p>
                <p><strong>Period:</strong> ${startdate} to ${enddate}</p>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="holdAmount" class="form-label">
                                    <strong>Hold Amount (for chargeback protection)</strong>
                                    <small class="text-muted d-block">Enter amount to hold. Remaining will be settled to payout wallet.</small>
                                </label>
                                <input type="number" class="form-control" id="holdAmount" 
                                       value="0" min="0" max="${parseFloat(amount)}" step="0.01"
                                       oninput="updateSettlementAmount(${parseFloat(amount)})">
                                <small class="text-muted">Maximum: ₹${parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</small>
                            </div>
                            <div class="mb-3">
                                <label for="settlementDescription" class="form-label">
                                    <strong>Description (Optional)</strong>
                                    <small class="text-muted d-block">Add a description for this settlement transaction.</small>
                                </label>
                                <textarea class="form-control" id="settlementDescription" rows="3" placeholder="Enter settlement description..."></textarea>
                            </div>
                            <div class="alert alert-info">
                                <strong>Settlement Breakdown:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Net Amount: ₹${parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</li>
                                    <li>Hold Amount: <span id="holdDisplay">₹0.00</span></li>
                                    <li><strong>Amount to Payout: <span id="settleDisplay">₹${parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span></strong></li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" onclick="processSettlement('${utr}', '${userid}', ${parseFloat(amount)}, ${parseFloat(bamount)}, ${parseFloat(tax)}, '${startdate}', '${enddate}')">
                                <i class="fas fa-check me-1"></i>Approve Settlement
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#settlementModal').remove();
        
        // Add modal to body
        $('body').append(modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('settlementModal'));
        modal.show();
        
        // Initialize hold amount display
        updateSettlementAmount(parseFloat(amount));
    }
    
    function updateSettlementAmount(netAmount) {
        const holdAmount = parseFloat($('#holdAmount').val()) || 0;
        const settleAmount = Math.max(0, netAmount - holdAmount);
        
        $('#holdDisplay').text('₹' + holdAmount.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        $('#settleDisplay').text('₹' + settleAmount.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    }
    
    function processSettlement(utr, userid, amount, bamount, tax, startdate, enddate) {
        const holdAmount = parseFloat($('#holdAmount').val()) || 0;
        const settleAmount = Math.max(0, amount - holdAmount);
        const description = $('#settlementDescription').val() || '';
        
        if (holdAmount > amount) {
            alert('Hold amount cannot be greater than net amount!');
            return;
        }
        
        if (confirm(`Confirm settlement?\n\nHold Amount: ₹${holdAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})}\nAmount to Payout: ₹${settleAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})}`)) {
        let data = new FormData();
            data.append('_token', '{{csrf_token()}}');
            data.append('userid', userid);
            data.append('amount', settleAmount); // Amount to settle to payout
            data.append('hold_amount', holdAmount); // Amount to hold
            data.append('utr', utr);
            data.append('bamount', bamount);
            data.append('tax', tax);
            data.append('startdate', startdate);
            data.append('enddate', enddate);
            data.append('description', description);
            
            // Close modal
            $('#settlementModal').modal('hide');
            
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            button.disabled = true;
            
        apex("POST", "{{ url('/admin/api/settlement/approve') }}", data, '', "#", "#");
            
            // Reset button after a delay
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 3000);
        }
    }

    function delete_category(id) {
        if (confirm('Are you sure you want to delete this item?')) {
        let data = new FormData();
            data.append('_token', '{{csrf_token()}}');
            data.append('id', id);
        apex("POST", "{{ url('/admin/api/delete_club_user') }}", data, '', "javascript:void(0)", "#");
            $("#table" + id).remove();
        }
    }

    // Format numbers with Indian locale
    function formatIndianCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(amount);
    }
</script>
@endsection
