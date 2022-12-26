<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement('ALTER TABLE transactions MODIFY COLUMN type ENUM("Normal", "Demokit", "Display", "Bebas Putus", "Sparepart", "Stock") DEFAULT "Normal"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE transactions MODIFY COLUMN type ENUM("Normal", "Demokit", "Display", "Bebas Putus") DEFAULT "Normal"');
    }
};
