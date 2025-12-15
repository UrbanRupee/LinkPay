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
                                <a href="tel:+"></a>
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
                                    Donation Amount
                                </h2>
                            </div>
                            <div id="form-validation" class="p-3">
                                <div class="preview">
                                    <form class="validate-form" id="edit_profile">
                                        @csrf
                                        <input type="hidden" name="type" value="{{$type}}">
                                        <div class="input-form">
                                            <label for="userid"
                                                class="form-label w-full flex flex-col sm:flex-row">
                                                User ID
                                            </label>
                                            <input type="text" id="userid" class="form-control" placeholder="Enter User ID" value="{{user('userid')}}" disabled readonly/>
                                        </div>
                                        <div class="input-form mt-3">
                                            <label for="amount"
                                                class="form-label w-full flex flex-col sm:flex-row">
                                                Amount to be Donate
                                            </label>
                                            <input type="number" name="amount" id="amount" class="form-control"
                                                placeholder="Amount to be donate" required/>
                                        </div>
                                        <div class="input-form mt-3">
                                            <label for="transaction_password"
                                                class="form-label w-full flex flex-col sm:flex-row">
                                                Transaction Password
                                            </label>
                                            <input class="form-control" type="password" name="transaction_password" id="transaction_password"
                                                placeholder="Enter Transaction Password" required/>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-5">
                                            Donate Now
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
        $("#edit_profile").validate({
            submitHandler: function(form) {
                apex("POST", "{{ url('/api/donation') }}", new FormData(form), form,
                    "{{ url('dashboard') }}",
                    "#");
            }
        });
    </script>
@endsection
