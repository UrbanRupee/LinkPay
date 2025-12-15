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
                                        <th>Email</th>
                                        <th>Mobile No</th>
                                        <th>Password</th>
                                        <th>Wallet</th>
                                        <th>P.In.Wallet</th>
                                        <th>P.Out.Wallet</th>
                                        <th>Hold.Wallet</th>
                                        <th>Percentage</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
    @php
        $TotalWalletPayIn = 0;
        $TotalWalletPayOut = 0;
        $TotalWallethold = 0;
    @endphp

    @if (count($user) > 0)
        @foreach ($user as $item)
            @php
                $WalletMain = isset($item->wallet->wallet) ? (float) $item->wallet->wallet : 0;
                $PayIn      = isset($item->wallet->payin) ? (float) $item->wallet->payin : 0;
                $PayHold    = isset($item->wallet->hold) ? (float) $item->wallet->hold : 0;
                $Payout     = isset($item->wallet->payout) ? (float) $item->wallet->payout : 0;

                $TotalWalletPayIn  += $PayIn;
                $TotalWalletPayOut += $Payout;
                $TotalWallethold   += $PayHold;
            @endphp
            <tr id="table{{ $item->userid }}">
                <th>{{ $loop->iteration }}</th>
                <td>
                    @if($item->role == 'agent')
                        <img src="/assets/images/franchise.png" alt="">
                    @endif
                    <a href="javascript:void;">{{ $item->userid }}</a>
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->mobile }}</td>
                <td>{{ $item->data2 }}</td>
                <td>{{ balance($WalletMain) }}</td>
                <td>{{ balance($PayIn) }}</td>
                <td>{{ balance($Payout) }}</td>
                <td>{{ balance($PayHold) }}</td>
                <td>{{ $item->percentage }}%</td>
                <td>
                    <button class="btn btn-{{$item->status==1?'success':'danger'}}"
                        onclick="updatestatus('{{ $item->userid }}','{{$item->status}}')">
                        {{$item->status==1?'Active':'Block'}}
                    </button>
                    @if(admin('role') == "admin")
                        <button class="btn btn-info"
                            onclick="redirect('/admin/user/login/{{$item->userid}}')">
                            Login
                        </button>
                    @endif
                    <button class="btn btn-info"
                        onclick="redirect('/admin/user/user-ledger/{{$item->userid}}')">
                        IN Ledger
                    </button>
                    <button class="btn btn-info"
                        onclick="redirect('/admin/user/payout-ledger/{{$item->userid}}')">
                        OUT Ledger
                    </button>
                    @if(admin('role') == "admin")
                        <button class="btn btn-warning"
                            onclick="redirect('/admin/userlist/edit/{{ $item->userid }}')">
                            Edit
                        </button>
                        <button class="btn btn-danger"
                            onclick="delete_category('{{ $item->userid }}')">
                            Delete
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
                            <button class="btn btn-success">Total Payin Wallet: {{$TotalWalletPayIn}}</button>
                            <button class="btn btn-success">Total PayOut Wallet: {{$TotalWalletPayOut}}</button>
                            <button class="btn btn-success">Total Hold Wallet: {{$TotalWallethold}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    function updatestatus(id,status) {
        let statuss = null;
        if (status == 0) {
            statuss = 'activate';
        }else if (status == 1) {
            statuss = 'deactivate';
        }
        let message = confirm('Are you sure you want '+statuss+' this user?');
        if (message) {
        let data = new FormData();
        data.append('_token','{{csrf_token()}}');
        data.append('id',id);
        apex("POST", "{{ url('/admin/api/user/block/') }}/"+id, data, '', "/admin/userlist", "#");
        }
    }
    function makefranchise(id) {
        let message = confirm('Are you sure you want to make franchise this user?');
        if (message) {
        let data = new FormData();
        data.append('_token','{{csrf_token()}}');
        data.append('id',id);
        apex("POST", "{{ url('/admin/api/user/becomefranchise/') }}/"+id, data, '', "/admin/userlist", "#");
        }
    }
    function delete_category(id) {
        let message = confirm('Are you sure you want to delete this user?');
        if (message) {
            let data = new FormData();
            data.append('_token','{{csrf_token()}}');
            data.append('id',id);
            apex("POST", "{{ url('/admin/api/delete_user') }}", data, '', "#", "#");
            $("#table"+id).remove();
        }
    }
</script>
@endsection
