<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmployeeStatusGrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_status_grades', function (Blueprint $table) {
            $table->integer('tenant_id');
            $table->integer('company_id');
            $table->string('grade_code', 20)->nullable(true);
            $table->integer('status_employee_id');
            $table->string('payroll_group_code', 20);
            $table->string('benefit_group_code', 20)->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_status_grades');
    }
}
