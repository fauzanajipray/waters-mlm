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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_numb')->unique();
            $table->enum('id_card_type', ['KTP', 'SIM'])->nullable();
            $table->string('id_card')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('level_id')->nullable();
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('restrict')->onUpdate('cascade');
            $table->string('gender')->nullable();
            $table->integer('postal_code')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->date('join_date')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->string('photo_url')->nullable();
            $table->unsignedBigInteger('upline_id')->nullable();
            $table->foreign('upline_id')->references('id')->on('members')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('members');
    }
};
