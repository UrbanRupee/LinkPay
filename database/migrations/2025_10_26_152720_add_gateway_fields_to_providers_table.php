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
        Schema::table('providers', function (Blueprint $table) {
            // Add gateway-specific fields if they don't exist
            if (!Schema::hasColumn('providers', 'provider_name')) {
                $table->string('provider_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('providers', 'gateway_id')) {
                $table->string('gateway_id')->nullable()->after('provider_name');
            }
            if (!Schema::hasColumn('providers', 'gateway_type')) {
                $table->enum('gateway_type', ['payin', 'payout', 'both'])->default('both')->after('gateway_id');
            }
            if (!Schema::hasColumn('providers', 'api_key')) {
                $table->text('api_key')->nullable()->after('gateway_type');
            }
            if (!Schema::hasColumn('providers', 'api_secret')) {
                $table->text('api_secret')->nullable()->after('api_key');
            }
            if (!Schema::hasColumn('providers', 'merchant_id')) {
                $table->string('merchant_id')->nullable()->after('api_secret');
            }
            if (!Schema::hasColumn('providers', 'environment')) {
                $table->enum('environment', ['test', 'prod'])->default('prod')->after('merchant_id');
            }
            if (!Schema::hasColumn('providers', 'callback_url')) {
                $table->text('callback_url')->nullable()->after('environment');
            }
            if (!Schema::hasColumn('providers', 'webhook_url')) {
                $table->text('webhook_url')->nullable()->after('callback_url');
            }
            if (!Schema::hasColumn('providers', 'priority')) {
                $table->integer('priority')->default(1)->after('status');
            }
            if (!Schema::hasColumn('providers', 'notes')) {
                $table->text('notes')->nullable()->after('priority');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            $columns = [
                'provider_name', 'gateway_id', 'gateway_type', 'api_key', 'api_secret',
                'merchant_id', 'environment', 'callback_url', 'webhook_url', 'priority', 'notes'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('providers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
