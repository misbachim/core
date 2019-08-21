<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLovNbftOnTableCfEmployeeProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('cf_employee_project', 'lov_nbft')) {
            Schema::table('cf_employee_project', function (Blueprint $table) {
                $table->string('lov_nbft', 30)->nullable(true);
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
        if (Schema::hasColumn('cf_employee_project', 'lov_nbft')) {
            Schema::table('cf_employee_project', function (Blueprint $table) {
                $table->dropColumn('lov_nbft');
            });
        }
    }
}
