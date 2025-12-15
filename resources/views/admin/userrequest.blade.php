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
                        <h6 class="card-title">{{$title}}</h6>
                        <div class="table-responsive">
                            <table class="table" id="dataTableExample">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Id</th>
                                        <th>Name</th>
                                        <th>Mobile No</th>
                                        <th>Amount</th>
                                        <th>UTR No.</th>
                                        <th>Request type</th>
                                        <th>Date Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($user) > 0)
                                        @foreach ($user as $item)
                                            <tr id="table{{ $item->userid }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td><a href="/admin/user/profile/{{ $item->userid }}" target="_blank">{{ $item->userid }}</a></td>
                                                <td>{{ userbyuserid($item->userid, 'name') }}</td>
                                                <td>{{ userbyuserid($item->userid, 'mobile') }}</td>
                                                <td>{{ number_format($item->amount,2) }}</td>
                                                <td>{{ $item->data2 }}</td>
                                                <td>{{ $item->type }}</td>
                                                <td>
                                                    {{dformat($item->created_at,'d-m-Y h:i:s')}}
                                                </td>
                                                <td>
                                                    @if ($item->status == 1)
                                                        <button class="btn btn-success">
                                                            Approved
                                                        </button>
                                                    @endif
                                                    @if ($item->status == 2)
                                                        <button class="btn btn-danger">
                                                            Cancelled
                                                        </button>
                                                    @endif
                                                    @if ($item->status == 0)
                                                        <button class="btn btn-success"
                                                            onclick="approve('{{ $item->id }}')">
                                                            Approve
                                                        </button>
                                                        <button class="btn btn-danger"
                                                            onclick="cancel('{{ $item->id }}')">
                                                            Decline
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center">No {{$title}} found!!</td>
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
    function approve(id) {
        let data = new FormData();
        data.append('_token','{{csrf_token()}}');
        data.append('id',id);
        apex("POST", "{{ url('/admin/api/user_request/approve') }}", data, '', "/admin/user-request", "#");
    }
    function cancel(id) {
        let data = new FormData();
        data.append('_token','{{csrf_token()}}');
        data.append('id',id);
        apex("POST", "{{ url('/admin/api/user_request/cancel') }}", data, '', "#", "#");
        $("#table"+id).remove();
    }
</script>
@endsection
