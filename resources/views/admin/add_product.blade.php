@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{$title}}</h6>
                        <form class="forms-sample" id="add_product">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" autocomplete="off"
                                    placeholder="Product name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach ($category as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="old_amount" class="form-label">Old Amount</label>
                                <input type="text" class="form-control" id="old_amount" autocomplete="off"
                                    placeholder="Total Amount" name="old_amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">After Discount Amount</label>
                                <input type="text" class="form-control" id="amount" autocomplete="off"
                                    placeholder="Exact Amount" name="amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea type="text" class="form-control" id="description" autocomplete="off"
                                    placeholder="Enter Description" name="description" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="specifications" class="form-label">Specifications</label>
                                <textarea type="text" class="form-control" id="specifications" autocomplete="off"
                                    placeholder="Enter specifications" name="specifications" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="image1" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image1" name="image1" required>
                            </div>
                            <div class="mb-3">
                                <label for="image2" class="form-label">Image2</label>
                                <input type="file" class="form-control" id="image2" name="image2">
                            </div>
                            <div class="mb-3">
                                <label for="image3" class="form-label">Image3</label>
                                <input type="file" class="form-control" id="image3" name="image3">
                            </div>
                            <div class="mb-3">
                                <label for="image4" class="form-label">Image4</label>
                                <input type="file" class="form-control" id="image4" name="image4">
                            </div>
                            <div class="mb-3">
                                <label for="image5" class="form-label">Image5</label>
                                <input type="file" class="form-control" id="image5" name="image5">
                            </div>
                            <div class="mb-3">
                                <label for="image6" class="form-label">Image6</label>
                                <input type="file" class="form-control" id="image6" name="image6">
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Add category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    formasync('add_product');
    $("#add_product").validate({
        submitHandler: function(form){
            apex("POST", "{{ url('/admin/api/add_product') }}", new FormData(form), form, "javascript:void(0)", "javascript:void(0)");
            $(form).trigger('reset');
        }
    });
</script>
@endsection
