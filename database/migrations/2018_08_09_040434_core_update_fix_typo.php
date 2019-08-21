<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateFixTypo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_statutes', function (Blueprint $table) {
            Schema::rename('employee_statutes', 'employee_statuses');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_statutes', function (Blueprint $table) {
            Schema::rename('employee_statuses', 'employee_statutes');
        });
    }
}
