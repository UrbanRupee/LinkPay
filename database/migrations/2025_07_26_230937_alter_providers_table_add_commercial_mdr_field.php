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
            // Remove the old boolean column
            $table->dropColumn('commercial');
            // Add the new string/text column for commercial_mdr
            $table->string('commercial_mdr')->nullable()->after('url'); // Or $table->text('commercial_mdr') for longer text
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
            // Revert to the old boolean column if you ever roll back
            $table->boolean('commercial')->default(false)->after('url');
            $table->dropColumn('commercial_mdr');
        });
    }
};
