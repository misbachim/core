<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWeightToEmployeeProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('employee_projects', 'weight')) {
            Schema::table('employee_projects', function (Blueprint $table) {
                        $table->float('weight')->nullable(true);
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
        if (Schema::hasColumn('employee_projects', 'weight')) {
            Schema::table('employee_projects', function (Blueprint $table) {
                $table->dropColumn('weight');
            });
        }
    }
}
