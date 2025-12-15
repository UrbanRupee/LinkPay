@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    @if ($level != null)
                        <div class="card-header">
                            <h5>{{ strtoupper(packages($level + 1, 'name')) }} Rank View</h5>
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
                                                    <td>{!! packages($item['package'], 'name') == 'Inactive'
                                                        ? html_entity_decode('<span class="btn btn-sm btn-danger">Inactive</span>')
                                                        : html_entity_decode(
                                                            '<span class="btn btn-sm btn-success">' .
                                                                strtoupper(packages($item['package'], 'name')) .
                                                                '</span>',
                                                        ) !!}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4" class="text-center">No Data Found</td>
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
                                            <th>#</th>
                                            <th>Rank</th>
                                            <th>Total Member</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 0; $i < count($ranks); $i++)
                                        <tr>
                                            <td>{{$i+1}}</td>
                                            <td>{{$ranks[$i]}}</td>
                                            <td>{{ count($data[$i]) }}</td>
                                            <td>
                                                <button class="btn btn-primary"
                                                    onclick="redirect('/user/level-rank/{{ $i }}')">View</button>
                                            </td>
                                        </tr>
                                        @endfor
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
