@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="intro-y flex items-center mt-2">
            <h2 class="text-lg font-medium mr-auto">Activation
            </h2>
        </div>
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 2xl:col-span-12">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 lg:col-span-4 mt-1">
                        <div class="intro-y col-span-12 md:col-span-4 mt-1 pb-4">
                            <div class="report-box-2">
                                <div class="box sm:flex">
                                    <div class="p-8 flex flex-col justify-center flex-1 profile-greeting">
                                        <div
                                            class="report-box-2__main-icon text-primary-card bg-info bg-opacity-20 border border-info border-opacity-20 flex items-center justify-center rounded-full">
                                            <i data-lucide="shopping-bag"></i>
                                        </div>
                                        <div class="flex items-center mt-[67px]">
                                            <div class="relative text-2xl font-medium card-price">
                                                {{balance(wallet(user('userid'),'int','wallet'))}}</div>
                                            <input type="hidden" id="fundWallet" value="0.00">
                                        </div>
                                        <div class="leading-relaxed mt-2   dark:text-slate-500"><span
                                                class="ava-blc">Available Balance.</span></div>
                                        <a href="{{ url('user/add-fund') }}" class="btn btn-info w-100 ">
                                            Add Fund
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y col-span-12 md:col-span-8 mt-1">
                        <div class="intro-y box">
                            <div
                                class="flex flex-col sm:flex-row items-center p-3 border-b border-slate-200/60 dark:border-darkmode-400">
                                <h2 class="font-medium text-base mr-auto">
                                    Activation
                                </h2>
                            </div>
                            <div id="form-validation" class="p-3">
                                <div class="preview">
                                    <form class="validate-form" id="epin_form">
                                        @csrf
                                        <div class="input-form">
                                            <label class="form-label w-full flex flex-col sm:flex-row">User ID
                                            </label>
                                            <input type="text" name="userid" id="userid" class="inp form-control" value="{{user('userid')}}"/>
                                            <div>
                                                <div id="username_container"></div>
                                            </div>
                                        </div>
                                        <div class="input-form mt-3">
                                            <label class="form-label w-full flex flex-col sm:flex-row">
                                                Package
                                            </label>
                                            <select class="form-control" name="package" id="package" onchange="selectpackage(this)">
                                                <option value="">Select Package</option>
                                                @foreach ($package as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ strtoupper($item->name)}} {{ $item->id == 1  ? ' (' . balance($item->amount) .')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="input-form mt-3" style="display:none;" id="stacking">
                                            <label class="form-label w-full flex flex-col sm:flex-row">
                                                Enter Amount
                                            </label>
                                            <input type="number" name="amount" id="amount" class="form-control" value="0" />
                                        </div>
                                        <div class="input-form mt-3">
                                            <label class="form-label w-full flex flex-col sm:flex-row">
                                                Enter Transaction Password
                                            </label>
                                            <input type="password" name="tpassword" id="tpassword" class="form-control" />
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-5">
                                            Active Now
                                        </button>
                                    </form>
                                </div>
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
         function selectpackage(aa){
             let a = $(aa).val();
             if(a == 2 || a== '2'){
                 $("#stacking").show();
             }else{
                 $("#stacking").hide();
             }
         }
        formasync('epin_form');
        $("#userid").on('blur', function() {
            let idcon = this;
            $.ajax({
                type: "post",
                url: "{{ url('/api/usercheck') }}",
                data: {
                    'userid': $(this).val()
                },
                dataType: "json",
                success: function(response) {
                    if (response.status == 1) {
                        $("#username_container").html(response.data);
                    } else {
                        $(idcon).val('');
                        $("#username_container").html(response.data);
                    }
                },
                error: function(e) {}
            });
        });
        $("#epin_form").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/api/upgrade_id') }}", new FormData(form), form,
                    "/dashboard",
                    "#");
            }
        });
    </script>
@endsection
