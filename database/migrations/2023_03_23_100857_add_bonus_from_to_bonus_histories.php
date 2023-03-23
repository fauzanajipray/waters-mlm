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
        Schema::table('bonus_histories', function (Blueprint $table) {
            $table->foreignId('bonus_from')->nullable()->after('bonus')->constrained('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_histories', function (Blueprint $table) {
            $table->dropForeign(['bonus_from']);
            $table->dropColumn('bonus_from');
        });
    }
};
