<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFdwServerAppraisal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE SERVER IF NOT EXISTS hr_appraisal
            FOREIGN DATA WRAPPER postgres_fdw
            OPTIONS (
                host \'' . config('database.connections.appraisal.host') . '\', 
                port \'' . config('database.connections.appraisal.port') . '\', 
                dbname \'' . config('database.connections.appraisal.database') . '\'
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
            DROP SERVER hr_appraisal
        ');
    }
}
