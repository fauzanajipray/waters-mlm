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
        Schema::create('level_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->integer('minimum_downline')->nullable(); // min. downline to upgrade level
            $table->integer('minimum_sold_by_downline')->nullable(); // min. sale by each downline to upgrade level
            $table->integer('minimum_sold')->nullable(); // min. sale to upgrade level
            $table->integer('ordering_level')->default(0);
            $table->double('bp_percentage')->default(0); // bonus for current level
            $table->double('gm_percentage')->default(0); // bonus for direct downline
            $table->double('or_percentage')->default(0); // bonus for group downline
            $table->double('or2_percentage')->default(0); // bonus for level 4 - 8 upline
            $table->date('date_start');
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
        Schema::dropIfExists('level_snapshots');
    }
};
