@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Direct Referal Team</h5>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive scroll-container">
                            <table class="table table-striped table-borderless table-vcenter">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Sponser Id</th>
                                        <th>Sponser Name</th>
                                        <th>Rank</th>
                                        <th>Joining Date</th>
                                        <th>Mobile No.</th>
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
                                                <td>{{ $item->mobile }}</td>
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
