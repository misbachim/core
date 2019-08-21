<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFdwServerTalent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE SERVER IF NOT EXISTS hr_talent
            FOREIGN DATA WRAPPER postgres_fdw
            OPTIONS (
                host \'' . config('database.connections.talent.host') . '\', 
                port \'' . config('database.connections.talent.port') . '\', 
                dbname \'' . config('database.connections.talent.database') . '\'
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
            DROP SERVER hr_talent
        ');
    }
}
