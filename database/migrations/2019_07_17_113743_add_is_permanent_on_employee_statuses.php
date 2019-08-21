<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPermanentOnEmployeeStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_statuses')) {
            Schema::table('employee_statuses', function (Blueprint $table) {
                $table->boolean('is_permanent')->default(false);
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
        if (Schema::hasTable('employee_statuses')) {
            Schema::table('employee_statuses', function (Blueprint $table) {
                $table->dropColumn('is_permanent');
            });
        }
    }
}
