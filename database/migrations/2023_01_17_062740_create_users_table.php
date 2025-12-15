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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string("userid");
            $table->string("name")->nullable();
            $table->string("email")->nullable();
            $table->string("mobile")->nullable();
            $table->string("address")->nullable();
            $table->string("token")->nullable();
            $table->string("isadmin")->nullable();
            $table->string("code")->nullable();
            $table->string("password");
            $table->string("data1")->nullable();
            $table->string("data2")->nullable();
            $table->string("data3")->nullable();
            $table->string("data4")->nullable();
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
        Schema::dropIfExists('users');
    }
};
