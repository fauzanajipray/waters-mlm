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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->unsignedBigInteger('branch_id');
            $table->enum('type', ['in', 'out', 'adjustment', 'sales']); // 'adjustment' is for manual adjustment, 'sales' is for sales, 'purchase' is for purchase
            $table->unsignedBigInteger('in_from')->nullable();
            $table->unsignedBigInteger('out_to')->nullable();
            $table->unsignedBigInteger('adjustment_by')->nullable();
            $table->unsignedBigInteger('sales_on')->nullable();
            $table->string('descriptions')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('in_from')->references('id')->on('branches');
            $table->foreign('out_to')->references('id')->on('branches');
            $table->foreign('adjustment_by')->references('id')->on('users');
            $table->foreign('sales_on')->references('id')->on('transactions');
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
        Schema::dropIfExists('stock_histories');
    }
};
