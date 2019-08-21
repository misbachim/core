<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFdwServer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE SERVER IF NOT EXISTS hr_um
            FOREIGN DATA WRAPPER postgres_fdw
            OPTIONS (
                host \'' . config('database.connections.um.host') . '\', 
                port \'' . config('database.connections.um.port') . '\', 
                dbname \'' . config('database.connections.um.database') . '\'
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
        // DB::unprepared('
        //     DROP SERVER hr_um
        // ');
    }
}
