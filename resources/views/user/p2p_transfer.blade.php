@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">{{$title}}
            </h2>
        </div>
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 2xl:col-span-12">
                <div class="grid grid-cols-12 gap-6">
                    <div class="intro-y col-span-12 md:col-span-4 mt-4 pb-4">
                        <div class="report-box-2">
                            <div class="box sm:flex">
                                <div class="p-8 flex flex-col justify-center flex-1 profile-greeting">
                                    <div
                                        class="report-box-2__main-icon text-primary-card bg-info bg-opacity-20 border border-info border-opacity-20 flex items-center justify-center rounded-full">
                                        <i data-lucide="shopping-bag"></i>
                                    </div>
                                    <div class="flex items-center mt-[67px]">
                                        <div class="relative text-2xl font-medium card-price">₹ {{wallet(user('userid'),null,'wallet')}}</div>
                                        <input type="hidden" id="fundWallet" value="0.00">
                                    </div>
                                    <div class="leading-relaxed mt-2   dark:text-slate-500"><span class="ava-blc">Withdrawal
                                            Wallet</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y col-span-12 md:col-span-8 mt-4">
                        <div class="intro-y box">
                            <div
                                class="flex flex-col sm:flex-row items-center p-3 border-b border-slate-200/60 dark:border-darkmode-400">
                                <h2 class="font-medium text-base mr-auto">
                                    Transfer P2P
                                </h2>
                            </div>
                            <div id="form-validation" class="p-3">
                                <div class="preview">
                                    <form class="validate-form" id="edit_profile">
                                        @csrf
                                        <div class="input-form">
                                            <label for="validation-form-1"
                                                class="form-label w-full flex flex-col sm:flex-row">
                                                Transferred to User ID
                                            </label>
                                            <input type="text" name="userid" id="userid" class="form-control" placeholder="Enter User ID" required/>
                                            <div class="help-block" id="username_container"></div>
                                        </div>
                                        <div class="input-form mt-3">
                                            <label for="validation-form-1"
                                                class="form-label w-full flex flex-col sm:flex-row">
                                                Amount to be Transferred
                                            </label>
                                            <input type="text" name="amount" id="amount" class="form-control"
                                                placeholder="Amount to be transfer" required/>
                                        </div>
                                        <div class="input-form mt-3">
                                            <label for="validation-form-1"
                                                class="form-label w-full flex flex-col sm:flex-row">
                                                Transaction Password
                                            </label>
                                            <input class="form-control" type="password" name="transaction_password" id="transaction_password"
                                                placeholder="Enter Transaction Password" required/>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-5">
                                            P2P Fund Transfer Now
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
        formasync('edit_profile');
        $("#userid").on('change', function() {
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
        $("#edit_profile").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/api/p2ptransfer') }}", new FormData(form), form,
                    "{{ url('user/p2p-transfer') }}",
                    "#");
            }
        });
    </script>
@endsection
