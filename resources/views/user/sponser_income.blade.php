@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ $title }}</h5>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive scroll-container">
                            <table class="table table-striped table-borderless table-vcenter">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Member Id</th>
                                        <th>Package</th>
                                        <th>Amount</th>
                                        <th>Date Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($list) > 0)
                                        @foreach ($list as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->userid }}</td>
                                                <td>{{ strtoupper($item->type) }}</td>
                                                <td>{{ balance($item->amount) }}</td>
                                                <td>{{ dformat($item->created_at, 'd-m-Y h:i:s') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No {{ $title }} found!!</td>
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
@endsection
