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
            $table->boolean('status_paid')->default(false);
        });
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        }); 
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('restrict');
            $table->dateTime('payment_date');
            $table->unsignedBigInteger('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict');
            $table->string('payment_name');
            $table->string('payment_account_number');
            $table->bigInteger('amount')->default(0);
            $table->string('photo_url')->nullable();
            $table->enum('type', ['Full', 'Partial'])->default('Full');
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
        Schema::dropIfExists('transaction_payments');
        Schema::dropIfExists('payment_methods');
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status_paid');
        });
    }
};
