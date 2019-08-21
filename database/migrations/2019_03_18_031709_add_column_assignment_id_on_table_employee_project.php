<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAssignmentIdOnTableEmployeeProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_projects', 'assignment_id')) {
                $table->integer('assignment_id')->default(1);
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
        Schema::table('employee_projects', function (Blueprint $table) {
            if (Schema::hasColumn('employee_projects', 'assignment_id')) {
                $table->dropColumn('assignment_id');
            }
        });
    }
}
