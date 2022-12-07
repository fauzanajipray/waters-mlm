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
        Schema::create('stock_out_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('branch_origin')->nullable();
            $table->unsignedBigInteger('branch_destination');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('branch_origin')->references('id')->on('branches');
            $table->foreign('branch_destination')->references('id')->on('branches');
            $table->timestamps();
        });

        Schema::create('stock_in_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('branch_origin');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('branch_origin')->references('id')->on('branches');
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
        Schema::dropIfExists('stock_in_histories');
        Schema::dropIfExists('stock_histories');
    }
};
