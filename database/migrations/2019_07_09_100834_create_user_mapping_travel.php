<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMappingTravel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE USER MAPPING IF NOT EXISTS FOR ' . config('database.connections.pgsql.username') . ' SERVER hr_travel
            OPTIONS (
                user \'' . config('database.connections.travel.username') . '\', 
                password \'' . config('database.connections.travel.password') . '\'
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
            DROP USER MAPPING FOR ' . config('database.connections.pgsql.username') . ' SERVER hr_travel
        ');
    }
}
