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
                                        <th>IP</th>
                                        <th>Limits</th>
                                        <th>Today hits</th>
                                        <th>Valid At</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($user) > 0)
                                        @foreach ($user as $item)
                                        
                                            <tr id="table{{ $item->id }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td>
                                                    <a href="javascript:void;">{{ $item->id }}</a>
                                                </td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->ip }}</td>
                                                <td>{{ $item->limits }}</td>
                                                <td>{{ $item->AllHitsToday }}</td>
                                                <td>{{ $item->valid_at }}</td>
                                                <td>
                                                    <button class="btn btn-{{$item->status==1?'success':'danger'}}"
                                                        onclick="updatestatus('{{ $item->id }}','{{$item->status}}')">
                                                        {{$item->status==1?'Active':'Block'}}
                                                    </button>
                                                </td>
                                                <td>
                                                    @if(admin('role') == "admin")
                                                    <button class="btn btn-warning"
                                                        onclick="redirect('/admin/userlist/edit/{{ $item->id }}')">
                                                        Edit
                                                    </button>
                                                    <button class="btn btn-danger"
                                                        onclick="delete_category('{{ $item->id }}')">
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
                            <button>Total Hitting Total: {{$TotalFirst+$TotalSecond+$TotalThird}}</button>
                            <button>Total Hitting First(Me): {{$TotalFirst}}</button>
                            <button>Total Hitting Second(Asif): {{$TotalSecond}}</button>
                            <button>Total Hitting Third(10k): {{$TotalThird}}</button>
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
