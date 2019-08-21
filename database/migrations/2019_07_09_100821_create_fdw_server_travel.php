<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFdwServerTravel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE SERVER IF NOT EXISTS hr_travel
            FOREIGN DATA WRAPPER postgres_fdw
            OPTIONS (
                host \'' . config('database.connections.travel.host') . '\', 
                port \'' . config('database.connections.travel.port') . '\', 
                dbname \'' . config('database.connections.travel.database') . '\'
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
            DROP SERVER hr_travel
        ');
    }
}
