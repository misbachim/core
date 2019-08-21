<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayrollGroupAndBenefitGroupCodeToEmployeeStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_statuses', 'payroll_group_code')) {
                $table->string('payroll_group_code', 20)->nullable(true);
            }
            if (!Schema::hasColumn('employee_statuses', 'benefit_group_code')) {
                $table->string('benefit_group_code', 20)->nullable(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('employee_statuses', 'payroll_group_code')) {
                $table->dropColumn('payroll_group_code');
            }
            if (Schema::hasColumn('employee_statuses', 'benefit_group_code')) {
                $table->dropColumn('benefit_group_code');
            }
        });
    }
}
