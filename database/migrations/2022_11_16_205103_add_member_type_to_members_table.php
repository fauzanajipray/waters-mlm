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
        Schema::table('members', function (Blueprint $table) {
            $table->enum('member_type', ['PERSONAL', 'STOKIST', 'CABANG', 'NIS', 'PUSAT'])->default('PERSONAL')->after('address');
            $table->boolean('lastpayment_status')->after('member_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('member_type');
            $table->dropColumn('lastpayment_status');
        });
    }
};
