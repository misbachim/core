<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMappingAppraisal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE USER MAPPING IF NOT EXISTS FOR ' . config('database.connections.pgsql.username') . ' SERVER hr_appraisal
            OPTIONS (
                user \'' . config('database.connections.appraisal.username') . '\', 
                password \'' . config('database.connections.appraisal.password') . '\'
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
            DROP USER MAPPING FOR ' . config('database.connections.pgsql.username') . ' SERVER hr_appraisal
        ');
    }
}
