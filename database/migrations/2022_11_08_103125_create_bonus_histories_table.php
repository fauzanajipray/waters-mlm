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
        Schema::create('bonus_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->string('member_numb');
            $table->foreignId('transaction_id')->constrained();
            $table->foreignId('level_id')->constrained();
            $table->enum('bonus_type', ['BP', 'BS', 'OR']);
            $table->decimal('bonus_percent', 20, 2)->default(0);
            $table->decimal('bonus', 20, 2)->default(0);
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
        Schema::dropIfExists('bonus_histories');
    }
};
