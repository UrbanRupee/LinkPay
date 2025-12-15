@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{$title}}</h5>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive scroll-container">
                            <table class="table table-striped table-borderless table-vcenter">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Member Id</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Credited at</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($list) > 0)
                                        @foreach ($list as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->userid }}</td>
                                                <td>{{ balance($item->amount) }}
                                                    @if ($item->data2 != '' && $item->data2 != null)
                                                        <br>
                                                        <p class="bg-success"
                                                            style="width:max-content; color:white; border-radius:5px; padding 2px 8px;padding:2px 5px">
                                                            {{ balance($item->data2) }}</p>
                                                    @endif
                                                </td>
                                                @php
                                                    if ($item->status == 1) {
                                                        $color = 'warning';
                                                        $name = 'Pending';
                                                    } elseif ($item->status == 0) {
                                                        $color = 'success';
                                                        $name = 'Credited';
                                                    } else {
                                                        $color = 'danger';
                                                        $name = 'Decline';
                                                    }
                                                @endphp
                                                <td><span
                                                        class="btn btn-sm btn-{{ $color }}">{{ ucfirst($name) }}</span>
                                                </td>
                                                <td>{{ dformat($item->created_at, 'd-m-Y h:i:s') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No {{ $title }} found!!</td>
                                        </tr>
                                    @endif
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
