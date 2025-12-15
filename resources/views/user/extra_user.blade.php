@extends('user.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{ $title }}</h6>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Sponser Id</th>
                                        <th>Sponser Name</th>
                                        <th>Rank</th>
                                        <th>Joining Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($list) > 0)
                                        @foreach ($list as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->userid }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{!! packages($item->package, 'name') == 'Inactive'
                                                    ? html_entity_decode('<span class="btn btn-sm btn-danger">Inactive</span>')
                                                    : html_entity_decode(
                                                        '<span class="btn btn-sm btn-success">' . strtoupper(packages($item->package, 'name')) . '</span>',
                                                    ) !!}</td>
                                                <td>{{ dformat($item->created_at, 'd-m-Y') }}</td>
                                                <td><button class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal{{$item->id}}">Transfer</button></td>
                                            </tr>
                                            <!-- Withdrawal Modal -->
        <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Withdrawal Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="forms-sample" id="withdrawal_amount">
                            @csrf
                            <div class="mb-3">
                                <label for="exampleInputUsername1" class="form-label">Available Amount</label>
                                <input type="text" class="form-control" placeholder="Amount" value="{{wallet(user('userid'), null, 'wallet')}}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Enter Withdrawal Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter Withdrawal Amount">
                            </div>
                            <div class="mb-3">
                                <label for="tpassword" class="form-label">Transaction Password</label>
                                <input type="password" class="form-control" id="tpassword" name="tpassword" placeholder="Transaction Password">
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center">No {{ $title }} found!!</td>
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
        formasync('edit_profile');
        $("#userid").on('change', function() {
            let idcon = this;
            $.ajax({
                type: "post",
                url: "{{ url('/api/usercheck') }}",
                data: {
                    'userid': $(this).val()
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 1) {
                        $("#username_container").html(response.data);
                    } else {
                        $(idcon).val('');
                        $("#username_container").html(response.data);
                    }
                },
                error: function(e) {}
            });
        });
        $("#edit_profile").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/api/upgrade_id') }}", new FormData(form), form,
                    "{{ url('dashboard') }}",
                    "#");
            }
        });
    </script>
@endsection
