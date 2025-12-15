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
        Schema::table('users', function (Blueprint $table) {
            // Credit Card type-specific percentages
            $table->decimal('cc_master_percentage', 5, 2)->nullable()->after('cc_percentage');
            $table->decimal('cc_visa_percentage', 5, 2)->nullable()->after('cc_master_percentage');
            $table->decimal('cc_rupay_percentage', 5, 2)->nullable()->after('cc_visa_percentage');
            $table->decimal('cc_others_percentage', 5, 2)->nullable()->after('cc_rupay_percentage');
            
            // Debit Card type-specific percentages
            $table->decimal('dc_master_percentage', 5, 2)->nullable()->after('dc_percentage');
            $table->decimal('dc_visa_percentage', 5, 2)->nullable()->after('dc_master_percentage');
            $table->decimal('dc_rupay_percentage', 5, 2)->nullable()->after('dc_visa_percentage');
            $table->decimal('dc_others_percentage', 5, 2)->nullable()->after('dc_rupay_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cc_master_percentage',
                'cc_visa_percentage',
                'cc_rupay_percentage',
                'cc_others_percentage',
                'dc_master_percentage',
                'dc_visa_percentage',
                'dc_rupay_percentage',
                'dc_others_percentage',
            ]);
        });
    }
};
