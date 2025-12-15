<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('upi_percentage', 5, 2)->nullable()->after('percentage');
            $table->decimal('cc_percentage', 5, 2)->nullable()->after('upi_percentage');
            $table->decimal('dc_percentage', 5, 2)->nullable()->after('cc_percentage');
            $table->decimal('nb_percentage', 5, 2)->nullable()->after('dc_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'upi_percentage',
                'cc_percentage',
                'dc_percentage',
                'nb_percentage',
            ]);
        });
    }
};






