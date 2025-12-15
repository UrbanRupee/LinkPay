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
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">{{ $title }}</h6>
                        <div class="table-responsive">
                            <table class="table" id="dataTableExample">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Id</th>
                                        <th>Name</th>
                                        <th>Bank Detail</th>
                                        <th>Payable Amount</th>
                                        <th>Date Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($user) > 0)
                                        @foreach ($user as $item)
                                        <tr id="table{{ $item->userid }}">
                                          <th>{{ $loop->iteration }}</th>
                                          <td><a href="/admin/user/profile/{{ $item->userid }}"
                                                  target="_blank">{{ $item->userid }}</a></td>
                                          <td>{{ userbyuserid($item->userid, 'name') }}</td>
                                          <td><button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                  data-bs-target="#exampleModal{{ $item->id }}">Bank
                                                  Detail</button></td>
                                          <th>{{ number_format($item->amount, 2) }}</th>
                                          <td>
                                              {{ dformat($item->created_at, 'd-m-Y') }}
                                          </td>
                                          <td>
                                              @if ($item->status == 1)
                                                  <button class="btn btn-success">
                                                      Approved
                                                  </button>
                                              @endif
                                              @if ($item->status == 2)
                                                  <button class="btn btn-danger">
                                                      Cancelled
                                                  </button>
                                              @endif
                                              @if ($item->status == 0)
                                                  <button class="btn btn-success"
                                                      onclick="approve('{{ $item->id }}')">
                                                      Approve
                                                  </button>
                                                  <button class="btn btn-danger"
                                                      onclick="cancel('{{ $item->id }}')">
                                                      Decline
                                                  </button>
                                              @endif
                                          </td>
                                      </tr>
                                      {{-- <!-- Bank detail Modal --> --}}
                                      <div class="modal fade" id="exampleModal{{ $item->id }}" tabindex="-1"
                                          aria-labelledby="exampleModalLabel" aria-hidden="true">
                                          <div class="modal-dialog modal-dialog-centered">
                                              <div class="modal-content">
                                                  <div class="modal-header">
                                                      <h5 class="modal-title" id="exampleModalLabel">Withdrawal
                                                          Request</h5>
                                                      <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                          aria-label="btn-close"></button>
                                                  </div>
                                                  <div class="modal-body">
                                                      <form class="forms-sample" id="withdrawal_amount">
                                                          @csrf
                                                          <div class="mb-3">
                                                              <label for="exampleInputUsername1"
                                                                  class="form-label">Payable Amount</label>
                                                              <input type="text" class="form-control"
                                                                  placeholder="Amount"
                                                                  value="{{ number_format($item->amount,2) }}"
                                                                  readonly>
                                                          </div>
                                                          <div class="mb-3">
                                                              <label for="amount" class="form-label">MODE</label>
                                                              <input type="text" class="form-control" value="{{$item->mode}}" readonly>
                                                          </div>
                                                          <div class="mb-3">
                                                              <label for="amount" class="form-label">Account Holder Name</label>
                                                              <input type="text" class="form-control" value="{{$item->holder_name}}" readonly>
                                                          </div>
                                                          <div class="mb-3">
                                                              <label for="amount" class="form-label">Account No.</label>
                                                              <input type="text" class="form-control" value="{{$item->account_no}}" readonly>
                                                          </div>
                                                          <div class="mb-3">
                                                              <label for="amount" class="form-label">IFSC Code</label>
                                                              <input type="text" class="form-control" value="{{$item->ifsc_code}}" readonly>
                                                          </div>
                                                      </form>
                                                  </div>
                                                  <div class="modal-footer">
                                                      <button type="button" class="btn btn-secondary"
                                                          data-bs-dismiss="modal">Close</button>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center">No {{ $title }} found!!</td>
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
        function approve(id) {
    // 1. Prompt the user for the UTR number
    let utrInput = prompt("Please enter the UTR number for this withdrawal:");

    // 2. Check if the user entered a value (and didn't cancel)
    //    You might want stricter validation depending on UTR format requirements
    if (utrInput === null || utrInput.trim() === "") {
        alert("UTR number is required to approve the withdrawal. Operation cancelled.");
        return; // Stop the function if no UTR is entered or cancel is clicked
    }

    // 3. If UTR is provided, proceed with creating FormData
    let data = new FormData();
    data.append('_token', '{{ csrf_token() }}'); // Ensure this Blade syntax is processed correctly by your backend template engine
    data.append('id', id);
    data.append('utr', utrInput); // Add the UTR number to the data

    // 4. Call the apex function with the updated data
    //    Assuming 'apex' is a custom function in your project for AJAX calls
    apex("POST", "{{ url('/admin/api/approve/withdrawal') }}", data, '', "/admin/user/withdrawal-request", "#");
}

        function cancel(id) {
            let utrInput = prompt("Please enter the Remark:");

    // 2. Check if the user entered a value (and didn't cancel)
    //    You might want stricter validation depending on UTR format requirements
    if (utrInput === null || utrInput.trim() === "") {
        alert("Remark is required.");
        return; // Stop the function if no UTR is entered or cancel is clicked
    }
            let data = new FormData();
            data.append('_token', '{{ csrf_token() }}');
            data.append('id', id);
            data.append('remark', utrInput);
            apex("POST", "{{ url('/admin/api/cancel/withdrawal') }}", data, '', "#", "#");
            $("#table" + id).remove();
        }
    </script>
@endsection
