<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        
        // First, clean up duplicate transaction_ids by keeping only the first one
        // Use raw SQL to find and delete duplicates
        $connection->statement("
            DELETE pr1 FROM payment_requests pr1
            INNER JOIN payment_requests pr2 
            WHERE pr1.id > pr2.id 
            AND pr1.transaction_id = pr2.transaction_id
        ");
        
        \Log::info('Duplicate payment requests cleaned up before adding unique index');
        
        // Check if index already exists
        $indexExists = $connection->selectOne(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = 'payment_requests' 
             AND index_name = 'unique_transaction_id'",
            [$dbName]
        );
        
        // Add unique index if it doesn't exist
        if (!$indexExists || $indexExists->count == 0) {
            Schema::table('payment_requests', function (Blueprint $table) {
                $table->unique('transaction_id', 'unique_transaction_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropUnique('unique_transaction_id');
        });
    }
};
