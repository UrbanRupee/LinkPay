@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Category</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    @if ($data == null)
                    <div class="card-body">
                        <h6 class="card-title">Add category</h6>

                        <form class="forms-sample" id="add_category">
                            @csrf
                            <div class="mb-3">
                                <label for="exampleInputUsername1" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" autocomplete="off"
                                    placeholder="Category name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image" required>
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Add category</button>
                        </form>
                    </div>
                    @else
                    <div class="card-body">
                        <h6 class="card-title">Update category</h6>

                        <form class="forms-sample" id="update_category">
                            @csrf
                            <input type="text" name="id" value="{{$data->id}}" hidden readonly>
                            <div class="mb-3">
                                <label for="exampleInputUsername1" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" autocomplete="off"
                                    placeholder="Category name" value="{{$data->name}}" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" value="{{$data->image}}" name="image">
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Update category</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">All Category</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($category) > 0)
                                        @foreach ($category as $item)
                                            <tr id="table{{ $item->id }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td>{{ $item->name }}</td>
                                                <td><img src="{{ $item->image }}" alt="" style="width:50px;"></td>
                                                <td>
                                                    <button class="btn btn-info"
                                                        onclick="redirect('/admin/category/{{ $item->id }}')">Edit</button>
                                                    <button class="btn btn-danger"
                                                        onclick="delete_category('{{ $item->id }}')">Delete</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No category found!!</td>
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
    formasync('add_category');
    $("#add_category").validate({
        submitHandler: function(form){
            apex("POST", "{{ url('/admin/api/add_category') }}", new FormData(form), form, "/admin/category", "#");
        }
    });
    formasync('update_category');
    $("#update_category").validate({
        submitHandler: function(form){
            apex("POST", "{{ url('/admin/api/update_category') }}", new FormData(form), form, "/admin/category", "#");
        }
    });
    function delete_category(id) {
        let data = new FormData();
        data.append('_token','{{csrf_token()}}');
        data.append('id',id);
        apex("POST", "{{ url('/admin/api/delete_category') }}", data, '', "#", "#");
        $("#table"+id).remove();
    }
</script>
@endsection
