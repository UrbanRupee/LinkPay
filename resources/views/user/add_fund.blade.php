@extends('user.layout.users')
@section('css')
@endsection

@section('content')
    <div class="content">
        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">Fund Wallet
            </h2>
        </div>
        <div class="grid grid-cols-12 gap-6" style="margin-bottom:100px;">
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
                                        <div class="relative text-2xl font-medium card-price">
                                            {{ balance(wallet(user('userid'), 'int', 'wallet')) }}</div>
                                        <div class="report-box-2__indicator text-success tooltip cursor-pointer"
                                            title="Total Balance is {{ balance(wallet(user('userid'), 'int', 'wallet')) }}">
                                            +4.1% <i data-lucide="arrow-up" class="w-4 h-4 ml-0.5"></i>
                                        </div>
                                    </div>
                                    <div class="leading-relaxed mt-2   dark:text-slate-500">Available Balance.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="intro-y col-span-12 md:col-span-8 mt-4" style="margin-bottom:500px;">
                        <div class="intro-y box">
                            <div
                                class="flex flex-col sm:flex-row items-center p-3 border-b border-slate-200/60 dark:border-darkmode-400">
                                <h2 class="font-medium text-base mr-auto">
                                    Add Fund By Recharge
                                </h2>
                            </div>
                            <div id="form-validation" class="p-3">
                                <div class="preview">
                                    <form class="" id="add_fund" action="{{ url('/api/add_fund') }}" method="post">
                                        @csrf
                                        <div class="input-form">
                                            <label for="amount" class="form-label w-full flex flex-col sm:flex-row">
                                                Enter Amount
                                            </label>
                                            <input type="text" name="amount" id="amount" class="input form-control"
                                                placeholder="Amount in {{ setting('currency') }}" required />
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-5">
                                            Proceed to Add Fund
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
    $("#add_fund").validate();

    function reedem(id) {
        let data = new FormData();
        data.append('_token', '{{ csrf_token() }}');
        data.append('id', id);
        apex("POST", "{{ url('/api/e_pin/reedem') }}", data, '', "/dashboard", "#");
    }
</script>
@endsection
