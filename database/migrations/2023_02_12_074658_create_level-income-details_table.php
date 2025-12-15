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
        Schema::create('level_income_details', function (Blueprint $table) {
            $table->id();
            $table->string("userid");
            $table->string("level")->nullable();
            $table->string("package")->nullable();
            $table->string("lastperson")->default(0);
            $table->string("data1")->nullable();
            $table->string("data2")->nullable();
            $table->string("data3")->nullable();
            $table->string("data4")->nullable();
            $table->string("data5")->nullable();
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
        Schema::dropIfExists('level-income-details');
    }
};
