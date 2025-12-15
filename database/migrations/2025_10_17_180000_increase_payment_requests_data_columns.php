<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to modify columns without requiring doctrine/dbal
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data1 TEXT NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data2 TEXT NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data3 TEXT NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data4 TEXT NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data5 TEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to VARCHAR(255)
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data1 VARCHAR(255) NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data2 VARCHAR(255) NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data3 VARCHAR(255) NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data4 VARCHAR(255) NULL');
        DB::statement('ALTER TABLE payment_requests MODIFY COLUMN data5 VARCHAR(255) NULL');
    }
};

