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
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('userid')->nullable();
            $table->string('amount')->nullable();
            $table->string('tax')->nullable();
            $table->string('data1')->nullable();
            $table->string('data2')->nullable();
            $table->string('data3')->nullable();
            $table->string('data4')->nullable();
            $table->string('data5')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('payment_requests');
    }
};
