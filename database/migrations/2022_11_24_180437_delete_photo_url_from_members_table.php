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
            $table->dropColumn('photo_url');
            $table->string('bank_account')->nullable()->after('upline_id');
            $table->string('bank_name')->nullable()->after('bank_account');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('type');
            $table->integer('npwp')->nullable()->after('branch_id');
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
            $table->string('photo_url')->nullable();
            $table->dropColumn('bank_account');
            $table->dropColumn('bank_name');
            $table->dropColumn('bank_branch');
            $table->dropColumn('branch_id');
            $table->dropColumn('npwp');
        });
    }
};
