<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsPayrollProcessToPersonAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_assets', function (Blueprint $table) {
            $table->boolean('is_payroll_process')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('person_assets', 'is_payroll_process')) {
            Schema::table('person_assets', function (Blueprint $table) {
                $table->dropColumn('is_payroll_process');
            });
        }
    }
}
