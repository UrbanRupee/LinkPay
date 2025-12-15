<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update all userids from GWS prefix to GWSDK prefix
     *
     * @return void
     */
    public function up()
    {
        // List of tables that contain userid column
        $tables = [
            'users',
            'wallets',
            'transactions',
            'payment_requests',
            'payout_requests',
            'orders',
            'club_users',
            'user_bank_details',
            'user_requests',
            'investments',
            'donations',
            'card_transactions',
            'user__banks',
            'user__wishlists',
            'level-income-details',
            'transaction_passwords',
        ];

        // Update each table
        foreach ($tables as $table) {
            // Check if table exists and has userid column
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'userid')) {
                DB::statement("UPDATE `{$table}` SET `userid` = REPLACE(`userid`, 'GWS', 'GWSDK') WHERE `userid` LIKE 'GWS%'");
            }
        }

        // Also check for sponserid column in users table
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'sponserid')) {
            DB::statement("UPDATE `users` SET `sponserid` = REPLACE(`sponserid`, 'GWS', 'GWSDK') WHERE `sponserid` LIKE 'GWS%'");
        }

        // Also check for papa column in users table (admin reference)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'papa')) {
            DB::statement("UPDATE `users` SET `papa` = REPLACE(`papa`, 'GWS', 'GWSDK') WHERE `papa` LIKE 'GWS%'");
        }
    }

    /**
     * Reverse the migrations.
     * Revert GWSDK back to GWS
     *
     * @return void
     */
    public function down()
    {
        // List of tables that contain userid column
        $tables = [
            'users',
            'wallets',
            'transactions',
            'payment_requests',
            'payout_requests',
            'orders',
            'club_users',
            'user_bank_details',
            'user_requests',
            'investments',
            'donations',
            'card_transactions',
            'user__banks',
            'user__wishlists',
            'level-income-details',
            'transaction_passwords',
        ];

        // Revert each table
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'userid')) {
                DB::statement("UPDATE `{$table}` SET `userid` = REPLACE(`userid`, 'GWSDK', 'GWS') WHERE `userid` LIKE 'GWSDK%'");
            }
        }

        // Revert sponserid
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'sponserid')) {
            DB::statement("UPDATE `users` SET `sponserid` = REPLACE(`sponserid`, 'GWSDK', 'GWS') WHERE `sponserid` LIKE 'GWSDK%'");
        }

        // Revert papa
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'papa')) {
            DB::statement("UPDATE `users` SET `papa` = REPLACE(`papa`, 'GWSDK', 'GWS') WHERE `papa` LIKE 'GWSDK%'");
        }
    }
};
