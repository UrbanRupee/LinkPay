@extends('admin.layout.user')
@section('css')
<style>
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
    .badge-success { background-color: #28a745; color: #fff; }
    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-danger { background-color: #dc3545; color: #fff; }
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
            <!--<div class="col-md-12 grid-margin stretch-card">-->
            <!--    <div class="card">-->
            <!--        <div class="card-body">-->
            <!--            <h6 class="card-title">{{_("Send Income")}}</h6>-->
            <!--            <div class="table-responsive">-->
            <!--                <form class="forms-sample" id="sendclubincome">-->
            <!--                @csrf-->
            <!--                <div class="mb-3">-->
            <!--                    <label for="amount" class="form-label">Amount</label>-->
            <!--                    <input type="number" class="form-control" id="amount" placeholder="Enter Amount"-->
            <!--                        name="amount">-->
            <!--                </div>-->
            <!--                <button type="submit" class="btn btn-primary me-2">Send Amount</button>-->
            <!--            </form>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{$title}}</h6>
                        <div class="table-responsive">
                            <table class="table" id="dataTableExample">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Userid</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created at</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($data) > 0)
                                        @foreach ($data as $item)
                                        @if($item->amount > 0)
                                            <tr id="table{{ $item->id }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td>
                                                {{userbyuserid($item->userid,'name')}}
                                                <br>
                                                {{$item->userid}}
                                                </td>
                                                <td>{{ balance($item->amount) }}</td>
                                                <td style="color:{{ ($item->type) == 'credit'?'green':'red' }}">{{ strtoupper($item->type) }}</td>
                                                <td>{{ strtoupper($item->data2 ?? '—') }}</td>
                                                <td>{{ strtoupper($item->data3 ?? '—') }}</td>
                                                <td>{{ $item->data1 ?? '—' }}</td>
                                                <td>
                                                    @if($item->status == 1)
                                                        <span class="badge badge-success">Success</span>
                                                    @elseif($item->status == 0)
                                                        <span class="badge badge-warning">Pending</span>
                                                    @else
                                                        <span class="badge badge-danger">Failed</span>
                                                    @endif
                                                </td>
                                                <td>{{ dformat($item->created_at,'d-m-Y h:i A') }}</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="text-center">No {{$title}} found!!</td>
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
