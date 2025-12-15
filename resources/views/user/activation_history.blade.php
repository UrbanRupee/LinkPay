@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Activation History</h5>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive scroll-container">
                            <table class="table table-striped table-borderless table-vcenter">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Userid</th>
                                        <th>Activated By</th>
                                        <th>Package</th>
                                        <th>Amount</th>
                                        <th>Datetime</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($transaction) > 0)
                                        @foreach ($transaction as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->userid }}</td>
                                                <td>{!! $item->data1 == "" ? $item->userid : $item->data1.'<br/>'.user('name',$item->data1).'<br>' !!}{{user('role',$item->data1) == "franchise" ? 'Franchise' : ''}}</td>
                                                <td>{{packages($item->data2,'name')}} <br> <span style="color:blue;">{{$item->data3 != "" ? product($item->data3,'name').' | ₹ '.number_format(product($item->data3,'amount'),2) : ''}}</span></td>
                                                <td>{{ balance($item->data2 == "1" ? floatval(packages($item->data2, 'amount')) :  floatval($item->amount)) }} </td>
                                                <td>{{ dformat($item->created_at,'d-m-Y') }}</td>
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
