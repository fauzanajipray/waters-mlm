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
        Schema::create('level_up_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->bigInteger('old_level_id')->nullable();
            $table->bigInteger('new_level_id')->nullable();
            $table->string('old_level_code')->nullable();
            $table->string('new_level_code')->nullable();
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
        Schema::dropIfExists('level_up_histories');
    }
};
