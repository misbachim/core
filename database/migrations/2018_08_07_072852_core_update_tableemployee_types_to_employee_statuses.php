<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreUpdateTableemployeeTypesToEmployeeStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_types', function (Blueprint $table) {
            $table->dropPrimary('employee_types_pkey');
        });

        Schema::table('employee_types', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_types', 'id')) {
                $table->increments('id');
            }
        });

        Schema::rename('employee_types', 'employee_statutes');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('employee_statutes', 'employee_types');

        Schema::table('employee_types', function (Blueprint $table) {
            if (Schema::hasColumn('employee_types', 'id')) {
                $table->dropColumn('id');
            }
        });

        Schema::table('employee_types', function (Blueprint $table) {
            $table->primary(array('tenant_id', 'company_id', 'code'));
        });

    }
}
