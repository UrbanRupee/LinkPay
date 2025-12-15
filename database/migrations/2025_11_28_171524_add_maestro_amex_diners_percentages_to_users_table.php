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
            // Credit Card additional type-specific percentages
            $table->decimal('cc_maestro_percentage', 5, 2)->nullable()->after('cc_others_percentage');
            $table->decimal('cc_amex_percentage', 5, 2)->nullable()->after('cc_maestro_percentage');
            $table->decimal('cc_diners_percentage', 5, 2)->nullable()->after('cc_amex_percentage');
            
            // Debit Card additional type-specific percentages
            $table->decimal('dc_maestro_percentage', 5, 2)->nullable()->after('dc_others_percentage');
            $table->decimal('dc_amex_percentage', 5, 2)->nullable()->after('dc_maestro_percentage');
            $table->decimal('dc_diners_percentage', 5, 2)->nullable()->after('dc_amex_percentage');
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
                'cc_maestro_percentage',
                'cc_amex_percentage',
                'cc_diners_percentage',
                'dc_maestro_percentage',
                'dc_amex_percentage',
                'dc_diners_percentage',
            ]);
        });
    }
};
