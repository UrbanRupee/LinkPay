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
        Schema::create('club_users', function (Blueprint $table) {
            $table->id();
            $table->string("userid");
            $table->string("type");
            $table->string("data1")->nullable();
            $table->string("data2")->nullable();
            $table->string("data3")->nullable();
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
        Schema::dropIfExists('club_users');
    }
};
