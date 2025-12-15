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
        Schema::create('user__carts', function (Blueprint $table) {
            $table->id();
            $table->string("pid");
            $table->string("name");
            $table->string("quantity")->nullable();
            $table->string("discount")->nullable();
            $table->string("discount2")->nullable();
            $table->string("coupan_code")->nullable();
            $table->string("status")->nullable();
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
        Schema::dropIfExists('user__carts');
    }
};
