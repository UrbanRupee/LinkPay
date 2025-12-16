@extends('admin.layout.user')
@section('css')
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
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                            <h6 class="card-title mb-0">{{$title}}</h6>
                                @if(isset($totalCount) || isset($todayCount))
                                    <small class="text-muted">
                                        Total Settlements: <strong>{{ $totalCount ?? 0 }}</strong> | 
                                        Today's Settlements: <strong class="text-primary">{{ $todayCount ?? 0 }}</strong>
                                    </small>
                                @endif
                            </div>
                            <a href="{{ route('admin.export.settlement_list', request()->all()) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-file-export me-1"></i>Export CSV
                            </a>
                        </div>
                        
                        <!-- Date Filter Section -->
                        <div class="card mb-3" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                            <div class="card-body p-3">
                                <form method="GET" action="{{ url('/admin/settlement_list') }}" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small">From Date</label>
                                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $from_date ?? '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">To Date</label>
                                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $to_date ?? '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">User ID</label>
                                        <input type="text" name="userid_filter" class="form-control form-control-sm" placeholder="Search User ID" value="{{ $userid_filter ?? '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">UTR No.</label>
                                        <input type="text" name="utr_filter" class="form-control form-control-sm" placeholder="Search UTR" value="{{ $utr_filter ?? '' }}">
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter me-1"></i>Apply Filters
                                        </button>
                                        <a href="{{ url('/admin/settlement_list') }}?from_date={{ date('Y-m-d') }}&to_date={{ date('Y-m-d') }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-calendar-day me-1"></i>Today Only
                                        </a>
                                        <a href="{{ url('/admin/settlement_list') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i>Clear Filters
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table" id="dataTableExample">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Userid</th>
                                        <th>UTR No.</th>
                                        <th>Gross Amount</th>
                                        <th>Service Charge</th>
                                        <th>Paid Amount</th>
                                        <th>Hold Amount</th>
                                        <th>Description</th>
                                        <th>Created at</th>
                                        <th>Bank Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($data) > 0)
                                        @foreach ($data as $item)
                                        @if($item->amount >= 0)
                                            @php
                                                // data3 = grossAmount (without tax), data4 = taxAmount, data5 = holdAmount, amount = settledAmount (net - hold)
                                                // Gross Amount = grossAmount (data3) + taxAmount (data4)
                                                // Net Amount = grossAmount (data3) - taxAmount (data4) = actual net before hold
                                                // Paid Amount = amount (settled amount after hold deduction) - this is what was actually paid to payout
                                                // Hold Amount = data5
                                                $taxAmount = (float) ($item->data4 ?? 0);
                                                $holdAmount = (float) ($item->data5 ?? 0);
                                                $settledAmount = (float) ($item->amount ?? 0); // This is the actual paid amount (net - hold)
                                                
                                                // Calculate gross amount: if data3 is not set, calculate from settled + tax + hold
                                                $grossAmountWithoutTax = (float) ($item->data3 ?? 0);
                                                if ($grossAmountWithoutTax == 0) {
                                                    // Backward compatibility: calculate from settled amount + tax + hold
                                                    $grossAmountWithoutTax = $settledAmount + $taxAmount + $holdAmount;
                                                }
                                                
                                                $totalGross = $grossAmountWithoutTax + $taxAmount; // Total gross including tax
                                                $netAmount = $grossAmountWithoutTax - $taxAmount; // Net amount before hold
                                                
                                                $bankDetails = \App\Models\User_Bank::where('userid', $item->userid)->first();
                                                $bankName = $bankDetails ? ($bankDetails->bank_name ?? '—') : '—';
                                                $accountNo = $bankDetails ? ($bankDetails->account_no ?? '—') : '—';
                                                $ifscCode = $bankDetails ? ($bankDetails->ifsc_code ?? '—') : '—';
                                            @endphp
                                            <tr id="table{{ $item->id }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td>{{$item->userid}}</td>
                                                <td>{{ ($item->data2) }}</td>
                                                <td>{{ balance($totalGross) }}</td>
                                                <td>{{ balance($taxAmount) }}</td>
                                                <td>{{ balance($settledAmount) }}</td>
                                                <td>{{ balance($holdAmount) }}</td>
                                                <td>{{ $item->data1 ?? '—' }}</td>
                                                <td>{{ dformat($item->created_at,'d-m-Y H:i:s') }}</td>
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
                                            </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="text-center">No {{$title}} found!!</td>
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
<script>
    function delete_category(id) {
        let data = new FormData();
        data.append('_token','{{csrf_token()}}');
        data.append('id',id);
        apex("POST", "{{ url('/admin/api/delete_club_user') }}", data, '', "javascript:void(0)", "#");
        $("#table"+id).remove();
    }
</script>
@endsection
