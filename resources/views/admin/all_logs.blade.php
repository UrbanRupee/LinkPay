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
                        <div class="table-responsive">
                            <table class="table" id="dataTableExample">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Unique ID</th>
                                        <th>Value</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($product) > 0) {{-- Renamed $logs to $product in the controller --}}
                                        @foreach ($product as $item)
                                            <tr id="table{{ $item->id }}">
                                                <th>{{ $loop->iteration }}</th>
                                                <td>{{ $item->uniqueid }}</td>
                                                <td><pre style="white-space: pre-wrap;">{{ $item->value }}</pre></td>
                                                <td>{{ $item->created_at }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No {{$title}} found!!</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $product->links('pagination::bootstrap-5') }} {{-- Add pagination links if you're using Bootstrap 5 --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    function deleteLog(id) {
        if (confirm('Are you sure you want to delete this log?')) {
            let data = new FormData();
            data.append('_token','{{csrf_token()}}');
            data.append('id',id);
            // Assuming 'apex' is your custom AJAX function.
            // You might need to adjust the URL and success handling based on your actual API route for deleting logs.
            apex("POST", "{{ url('/admin/api/delete_log') }}", data, function(response) {
                if (response.status === 'success') { // Adjust based on your API response structure
                    $("#table"+id).remove();
                    // Optionally show a success message
                    alert('Log deleted successfully!');
                } else {
                    alert('Error deleting log: ' + response.message); // Adjust based on your API response structure
                }
            }, "#", "#"); // The last two parameters for 'apex' might be for loader/target elements
        }
    }
</script>
@endsection