@extends('user.layout.users')
@section('css')
<style>
    @media only screen and (max-width: 400px) {
  .ttt {
    font-size:10px;
  }
}
</style>
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    @if ($level != null)
                        <div class="card-header">
                            <h5>Level{{ $level }} View</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive scroll-container">
                                <table class="table table-striped table-borderless table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Member Id</th>
                                            <th>Name</th>
                                            <th>Package</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($ldata) > 0)
                                            @foreach ($ldata as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item['userid'] }}</td>
                                                    <td>{{ $item['name'] }}</td>
                                                    <td><span class="btn btn-sm btn-{{isActive($item['userid']) ? 'success' : 'danger'}}">{{isActive($item['userid'],'string')}}</span></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center">No Data Found</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="card-header">
                            <h5>{{ $title }}</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive scroll-container">
                                <table class="table table-striped table-borderless table-vcenter">
                                    <thead>
                                        <tr>
                                            <!--<th>#</th>-->
                                            <th style="width:100px;">Level Name</th>
                                            <th>Total Member</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($data) > 0)
                                            @foreach ($data as $item)
                                                <tr>
                                                    <!--<td>{{ $loop->iteration }}</td>-->
                                                    <td>Level {{$loop->iteration }}</td>
                                                    <td>{{ $item['tuser'] }}</td>
                                                    <td>
                                                        <button class="btn btn-primary"
                                                            onclick="redirect('/user/level-view/{{ $loop->iteration }}')">View</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center">No {{ $title }} Report
                                                    found!!</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection
