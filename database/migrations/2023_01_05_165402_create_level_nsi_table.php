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
        Schema::create('level_nsi', function (Blueprint $table) {
            $table->id();
            // range penjualan
            $table->integer('min_sold');
            // persentase bonus penjualan
            $table->double('bonus_percentage')->default(0);
            $table->timestamps();
        });

        // add level nsi to member
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('level_nsi_id')->nullable()->after('level_id');
            $table->foreign('level_nsi_id')->references('id')->on('level_nsi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('level_nsi');
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('level_nsi_id');
        });
    }
};
