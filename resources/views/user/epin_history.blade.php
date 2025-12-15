@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>E-Pin History</h5>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive scroll-container">
                            <table class="table table-striped table-borderless table-vcenter">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Userid</th>
                                        <th>Amount</th>
                                        <th>Pin</th>
                                        <th>Secret</th>
                                        <th>Datetime</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($transaction) > 0)
                                        @foreach ($transaction as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->userid }}</td>
                                                <td>{{ balance($item->amount )}}</td>
                                                <td>{{$item->pid}}</td>
                                                <td>{{$item->status == 1 ? $item->secret : '****'}}</td>
                                                <td>{{ dformat($item->created_at,'d-m-Y') }}</td>
                                                <td>{{$item->status == 1 ? 'Active' : 'Expire'}}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No {{ $title }} Report found!!</td>
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
