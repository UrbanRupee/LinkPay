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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('service_type')->nullable();
            $table->string('url')->nullable();
            $table->boolean('commercial')->default(false);
            $table->boolean('cards')->default(false);
            $table->boolean('apms')->default(false);
            $table->boolean('bank_transfer')->default(false);
            $table->boolean('in')->default(false);
            $table->boolean('out')->default(false);
            $table->string('settlement_timeline')->nullable();
            $table->string('settlement_mode')->nullable();
            $table->string('contact_spoc')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('risk_and_blacklisting')->nullable();
            $table->enum('status', ['active', 'inactive', 'hold'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers');
    }
};
