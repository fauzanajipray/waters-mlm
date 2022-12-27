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
        DB::statement('ALTER TABLE bonus_histories MODIFY COLUMN bonus_type ENUM("BP", "GM", "OR", "SS") ');
        Schema::table('bonus_histories', function (Blueprint $table) {
            $table->enum('ss_type', ['CABANG', 'STOKIST', 'MEMBER'])->nullable()->after('bonus_type');
            $table->unsignedBigInteger('ss_product_id')->nullable()->after('ss_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_histories', function (Blueprint $table) {
            $table->dropColumn('ss_type');
        });
        DB::statement('ALTER TABLE bonus_histories MODIFY COLUMN bonus_type ENUM("BP", "GM", "OR")');  
    }
};
