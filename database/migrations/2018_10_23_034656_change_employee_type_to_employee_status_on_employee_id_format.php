<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEmployeeTypeToEmployeeStatusOnEmployeeIdFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_id_formats', function (Blueprint $table) {
            $table->renameColumn('employee_type_code', 'employee_status_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_id_formats', function (Blueprint $table) {
            $table->renameColumn('employee_status_code', 'employee_type_code');
        });
    }
}
