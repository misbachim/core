<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyWorkingMonthColumnNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('employee_statuses', 'working_month')) {
            Schema::table('employee_statuses', function (Blueprint $table) {
                $table->smallInteger('working_month')->nullable(true)->change();
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
        if (Schema::hasColumn('employee_statuses', 'working_month')) {
            Schema::table('employee_statuses', function (Blueprint $table) {
                $table->smallInteger('working_month')->nullable(false)->change();
            });
        }
    }
}
