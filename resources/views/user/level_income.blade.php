@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                @if($package == null)
                <div class="card">
                    <div class="card-header">
                        <h5>{{ $title }}</h5>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive scroll-container">
                            <table class="table table-striped table-borderless table-vcenter">
                                <thead>
                                    <tr>
                                        <!--<th>#</th>-->
                                        <th>Package Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($pack) > 0)
                                        @foreach ($pack as $item)
                                        <tr>
                                            <!--<td>{{$loop->iteration}}</td>-->
                                            <td>{{strtoupper('Level '.$item)}}</td>
                                            <td><button class="btn btn-primary" onclick="redirect('/user/level-income/{{$item}}')">View</button></td>
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
                @else
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
                                        <th>Member id</th>
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
                                                <td>{{ $item->data2 }}</td>
                                                <td>{{ strtoupper($item->data4) }}</td>
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
                @endif
            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection
