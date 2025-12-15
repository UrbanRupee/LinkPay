<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$ids = ['BCOD31019','BCOD70674','BCOD80856','BCOD61775','BCOD46308'];
$records = App\Models\Payment_request::whereIn('transaction_id', $ids)->get();
foreach ($records as $pr) {
    App\Services\PayinFeeService::syncModeFees($pr);
    echo $pr->transaction_id . ' => ' . $pr->tax . PHP_EOL;
}
