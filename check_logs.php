<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Logs Table Structure ===\n";

// Get table structure
$columns = DB::select("DESCRIBE logs");
echo "Logs table columns:\n";
foreach($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\n=== Sample Log Entry ===\n";

// Get a sample log entry
$sample = DB::table('logs')->first();
if($sample) {
    echo "Sample log entry structure:\n";
    foreach($sample as $key => $value) {
        echo "- {$key}: " . (is_string($value) ? substr($value, 0, 50) . "..." : $value) . "\n";
    }
}

echo "\n=== Check Complete ===\n";


