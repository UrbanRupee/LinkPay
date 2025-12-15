@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <div class="row">
            @if ($data != null)
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Update category</h6>

                            <form class="forms-sample" id="update_category">
                                @csrf
                                <input type="text" name="id" value="{{ $data->id }}" hidden readonly>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" autocomplete="off"
                                        placeholder="Category name" value="{{ $data->name }}" name="name" required
                                        readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="value" class="form-label">Value</label>
                                    <textarea class="form-control" id="value"
                                        name="value">{{ $data->value }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary me-2">Update {{ $title }}</button>
                            </form>

                        </div>
                    </div>
                </div>
            @endif
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">All {{ $title }}</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Value</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($setting) > 0)
                                        @foreach ($setting as $item)
                                            <tr id="table{{ $item->id }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td>{{ strtoupper($item->name) }}</td>
                                                <td>{{ str_word_count($item->value) > 20 ? substr($item->value,0,22).'...' : $item->value }}</td>
                                                <td>
                                                    <button class="btn btn-info"
                                                        onclick="redirect('/admin/setting/{{ $item->id }}')">Edit</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No {{ $title }} found!!</td>
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
        formasync('update_category');
        $("#update_category").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/admin/api/update_setting') }}", new FormData(form), form,
                    "/admin/setting", "#");
            }
        });
    </script>
@endsection
