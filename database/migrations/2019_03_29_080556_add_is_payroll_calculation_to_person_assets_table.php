<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPayrollCalculationToPersonAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('person_assets', 'is_payroll_calculation')) {
            Schema::table('person_assets', function (Blueprint $table) {
                $table->boolean('is_payroll_calculation')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('person_assets', 'is_payroll_calculation')) {
            Schema::table('person_assets', function (Blueprint $table) {
                $table->dropColumn('is_payroll_calculation');
            });
        }
    }
}
