<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFdwServerPayroll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE SERVER IF NOT EXISTS hr_payroll
            FOREIGN DATA WRAPPER postgres_fdw
            OPTIONS (
                host \'' . config('database.connections.payroll.host') . '\', 
                port \'' . config('database.connections.payroll.port') . '\', 
                dbname \'' . config('database.connections.payroll.database') . '\'
            );
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('
            DROP SERVER hr_payroll
        ');
    }
}
