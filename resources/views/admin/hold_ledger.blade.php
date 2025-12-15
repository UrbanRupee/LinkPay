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
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="card-title mb-0">{{$title}}</h6>
                        </div>
                        <p class="text-muted mb-3">
                            This ledger shows all amounts held from settlements for chargeback protection.
                        </p>
                        <div class="table-responsive">
                            <table class="table" id="dataTableExample">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Userid</th>
                                        <th>UTR No.</th>
                                        <th>Gross Amount</th>
                                        <th>Service Charge</th>
                                        <th>Net Amount</th>
                                        <th>Hold Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Created at</th>
                                        <th>Bank Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($data) > 0)
                                        @foreach ($data as $item)
                                            @php
                                                // data3 = grossAmount, data4 = taxAmount, data5 = holdAmount, amount = settledAmount (net - hold)
                                                $grossAmount = (float) ($item->data3 ?? 0);
                                                $taxAmount = (float) ($item->data4 ?? 0);
                                                $holdAmount = (float) ($item->data5 ?? 0);
                                                $settledAmount = (float) ($item->amount ?? 0);
                                                $netAmount = $grossAmount - $taxAmount;
                                                $gross = $grossAmount + $taxAmount;
                                                $bankDetails = \App\Models\User_Bank::where('userid', $item->userid)->first();
                                                $bankName = $bankDetails ? ($bankDetails->bank_name ?? '—') : '—';
                                                $accountNo = $bankDetails ? ($bankDetails->account_no ?? '—') : '—';
                                                $ifscCode = $bankDetails ? ($bankDetails->ifsc_code ?? '—') : '—';
                                            @endphp
                                            <tr id="table{{ $item->id }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td>{{$item->userid}}</td>
                                                <td>{{ ($item->data2) }}</td>
                                                <td>{{ balance($gross) }}</td>
                                                <td>{{ balance($taxAmount) }}</td>
                                                <td>{{ balance($netAmount) }}</td>
                                                <td class="text-danger"><strong>{{ balance($holdAmount) }}</strong></td>
                                                <td>{{ balance($settledAmount) }}</td>
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
