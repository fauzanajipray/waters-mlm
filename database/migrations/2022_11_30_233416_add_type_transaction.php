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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', ['Normal', 'Demokit', 'Display', 'Bebas Putus'])->default('NORMAL');
        });
        // add discount percentage, discount amount, total amount
        Schema::table('transaction_products', function (Blueprint $table) {
            $table->double('discount_percentage')->default(0);
            $table->double('discount_amount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('transaction_products', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
            $table->dropColumn('discount_amount');
        });
    }
};
