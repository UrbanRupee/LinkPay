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
            // Card transaction configuration fields
            $table->decimal('card_percentage', 5, 2)->nullable()->default(2.50)->after('out_ip');
            $table->decimal('card_fixed_fee', 5, 2)->nullable()->default(0.30)->after('card_percentage');
            $table->string('card_callback')->nullable()->after('card_fixed_fee');
            $table->string('cardgateway')->nullable()->after('card_callback');
            $table->string('card_ip')->nullable()->after('cardgateway');
            $table->enum('card_status', ['active', 'inactive', 'hold'])->default('active')->after('card_ip');
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
            // Remove card transaction configuration fields
            $table->dropColumn([
                'card_percentage',
                'card_fixed_fee',
                'card_callback',
                'cardgateway',
                'card_ip',
                'card_status'
            ]);
        });
    }
};
