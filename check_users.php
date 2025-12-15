<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Users with FinQunes Gateways ===\n";

// Check PayIn Gateway (Flag 14)
$payinUsers = DB::table('users')->where('payingateway', '14')->select('userid', 'name', 'payingateway')->get();
echo "Users with PayIn Flag 14 (FinQunes):\n";
foreach($payinUsers as $user) {
    echo "- {$user->userid} - {$user->name} (Flag: {$user->payingateway})\n";
}
if($payinUsers->isEmpty()) {
    echo "No users found with PayIn Flag 14\n";
}

echo "\n";

// Check PayOut Gateway (Flag 12)
$payoutUsers = DB::table('users')->where('payoutgateway', '12')->select('userid', 'name', 'payoutgateway')->get();
echo "Users with PayOut Flag 12 (FinQunes):\n";
foreach($payoutUsers as $user) {
    echo "- {$user->userid} - {$user->name} (Flag: {$user->payoutgateway})\n";
}
if($payoutUsers->isEmpty()) {
    echo "No users found with PayOut Flag 12\n";
}

echo "\n";

// Check recent users and their gateway assignments
echo "Recent users and their gateway assignments:\n";
$recentUsers = DB::table('users')->select('userid', 'name', 'payingateway', 'payoutgateway')->orderBy('id', 'desc')->limit(5)->get();
foreach($recentUsers as $user) {
    echo "- {$user->userid} - {$user->name} (PayIn: {$user->payingateway}, PayOut: {$user->payoutgateway})\n";
}

echo "\n=== Check Complete ===\n";