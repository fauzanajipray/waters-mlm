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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('minimum_downline')->nullable(); // min. downline to upgrade level
            $table->integer('minimum_sold_by_downline')->nullable(); // min. sale by each downline to upgrade level
            $table->integer('minimum_sold')->nullable(); // min. sale to upgrade level
            $table->integer('ordering_level')->default(0);
            $table->double('bp_percentage')->default(0); // sold by current level
            $table->double('bs_percentage')->default(0); // sold by direct downline
            $table->double('or_percentage')->default(0); // sold by group downline
            $table->softDeletes();
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
        Schema::dropIfExists('levels');
    }
};
