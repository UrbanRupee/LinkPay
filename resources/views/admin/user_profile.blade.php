@extends('admin.layout.user')
@section('css')
@endsection

@section('content')
    <div class="page-content">
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="position-relative">
                        <div
                            class="d-flex justify-content-between align-items-center position-absolute top-90 w-100 px-2 px-md-4 mt-n4">
                            <div>
                                <img class="wd-70 rounded-circle" src="{{ $user->image }}" alt="profile">
                                <span class="h4 ms-3 text-dark">{{ $user->name }}</span>
                            </div>
                            <div class="d-none d-md-block">
                                <button class="btn btn-primary btn-icon-text"
                                    onclick="redirect('/admin/userlist/edit/{{ $user->userid }}')">
                                    <i data-feather="edit" class="btn-icon-prepend"></i> Edit profile
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center p-3 rounded-bottom">
                        <ul class="d-flex align-items-center m-0 p-0">
                            {{-- <li class="d-flex align-items-center active">
                                <i class="me-1 icon-md text-primary" data-feather="columns"></i>
                                <a class="pt-1px d-none d-md-block text-primary" href="#">Timeline</a>
                            </li> --}}
                            <li class="ms-3 ps-3 border-start d-flex align-items-center">
                                <i class="me-1 icon-md" data-feather="user"></i>
                                <a class="pt-1px d-none d-md-block text-body" href="#">About</a>
                            </li>
                            {{-- <li class="ms-3 ps-3 border-start d-flex align-items-center">
                                <i class="me-1 icon-md" data-feather="users"></i>
                                <a class="pt-1px d-none d-md-block text-body" href="#">Friends <span
                                        class="text-muted tx-12">3,765</span></a>
                            </li>
                            <li class="ms-3 ps-3 border-start d-flex align-items-center">
                                <i class="me-1 icon-md" data-feather="image"></i>
                                <a class="pt-1px d-none d-md-block text-body" href="#">Photos</a>
                            </li>
                            <li class="ms-3 ps-3 border-start d-flex align-items-center">
                                <i class="me-1 icon-md" data-feather="video"></i>
                                <a class="pt-1px d-none d-md-block text-body" href="#">Videos</a>
                            </li> --}}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row profile-body">
            <!-- left wrapper start -->
            <div class="d-none d-md-block col-md-4 col-xl-3 left-wrapper">
                <div class="card rounded">
                    <div class="card-body">
                        {{-- <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="card-title mb-0">About</h6>
                            <div class="dropdown">
                                <a type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i
                                            data-feather="edit-2" class="icon-sm me-2"></i> <span
                                            class="">Edit</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i
                                            data-feather="git-branch" class="icon-sm me-2"></i> <span
                                            class="">Update</span></a>
                                    <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i
                                            data-feather="eye" class="icon-sm me-2"></i> <span class="">View
                                            all</span></a>
                                </div>
                            </div>
                        </div> --}}
                        <p>Hi! I'm {{ $user->name }}, Member of {{setting('app_name')}} Family.</p>
                        <div class="mt-3">
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Joined:</label>
                            <p class="text-muted">{{ dformat($user->created_at, 'M d, Y') }}</p>
                        </div>
                        <div class="mt-3">
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Lives:</label>
                            <p class="text-muted">
                                {{ $user->address . ' ' . $user->city . ' ' . $user->state . ' ' . $user->pincode }}</p>
                        </div>
                        <div class="mt-3">
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Address 2:</label>
                            <p class="text-muted">{{ $user->address_2 }}</p>
                        </div>
                        <div class="mt-3">
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Email:</label>
                            <p class="text-muted">{{ $user->email }}</p>
                        </div>
                        <div class="mt-3">
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Mobile No:</label>
                            <p class="text-muted">+91 {{ $user->mobile }}</p>
                        </div>
                        <div class="mt-3">
                            <label class="tx-11 fw-bolder mb-0 text-uppercase">Secondary Mobile No:</label>
                            <p class="text-muted">{{ '+91 ' . $user->mobile_2 }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- left wrapper end -->
            <!-- middle wrapper start -->
            <div class="col-md-8 col-xl-6 middle-wrapper">
                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="card rounded">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img class="img-xs rounded-circle" src="{{ $user->image }}" alt="">
                                        <div class="ms-2">
                                            <p>{{ $user->name }}</p>
                                            <p class="tx-11 text-muted">{{ dformat($user->created_at, 'd-m-Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h4>Bank Account Detail--</h4><br>
                                <p class="mb-3 tx-14">
                                    Bank Name = {{ bank($user->userid, 'bank_name') }} <br>
                                    Account No. = {{ bank($user->userid, 'account_no') }} <br>
                                    IFSC Code. = {{ bank($user->userid, 'ifsc_code') }} <br>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 grid-margin">
                        <div class="card rounded">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img class="img-xs rounded-circle" src="{{ $user->image }}" alt="">
                                        <div class="ms-2">
                                            <p>{{ $user->name }}</p>
                                            <p class="tx-11 text-muted">{{ dformat($user->created_at, 'd-m-Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h4>Nominee Detail--</h4><br>
                                <p class="mb-3 tx-14">
                                    Nominee Name = {{ $user->nominee_name }} <br>
                                    Nominee Relation. = {{ $user->nominee_relation }} <br>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- middle wrapper end -->
            <!-- right wrapper start -->
            <div class="d-none d-xl-block col-xl-3">
                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="card rounded">
                            <div class="card-body">
                                <h6 class="card-title">Current Package Status</h6>
                                <div class="row ms-0 me-0">
                                    <a href="javascript:;" class="col-md-12 ps-1 pe-1">
                                        <button
                                            class="btn btn-{{ isActive($user->userid) ? 'success' : 'danger' }} w-100">{{ isActive($user->userid) ? 'Active' : 'Inactive' }}</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 grid-margin">
                        <div class="card rounded">
                            <div class="card-body">
                                <h6 class="card-title">Direct Member</h6>
                                @if (count($direct_team) > 0)
                                    @foreach ($direct_team as $item)
                                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                            <div class="d-flex align-items-center hover-pointer">
                                                <img class="img-xs rounded-circle" src="{{ $item->image }}"
                                                    alt="">
                                                <div class="ms-2">
                                                    <p><a href="/admin/user/profile/{{ $item->userid }}"
                                                            target="_blank">{{ $item->userid }}</a></p>
                                                    <p>{{ $item->name }}</p>
                                                    <p class="tx-11 text-muted">+91 {{ $item->mobile }}</p>
                                                </div>
                                            </div>
                                            <button
                                                class="btn btn-icon border-0 btn-sm">{{ isActive($user->userid) ? 'Active'  : 'Inactive' }}</button>
                                        </div>
                                    @endforeach
                                @else
                                    <h5 class="text-center">No Direct Team Found!!</h5>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- right wrapper end -->
        </div>

    </div>
@endsection

@section('js')
    <script>
        formasync('add_category');
        $("#add_category").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/admin/api/add_category') }}", new FormData(form), form,
                    "/admin/category", "#");
            }
        });
        formasync('update_category');
        $("#update_category").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/admin/api/update_category') }}", new FormData(form), form,
                    "/admin/category", "#");
            }
        });

        function delete_category(id) {
            let data = new FormData();
            data.append('_token', '{{ csrf_token() }}');
            data.append('id', id);
            apex("POST", "{{ url('/admin/api/delete_category') }}", data, '', "#", "#");
            $("#table" + id).remove();
        }
    </script>
@endsection
