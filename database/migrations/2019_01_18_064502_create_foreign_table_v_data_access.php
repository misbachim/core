<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignTableVDataAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FOREIGN TABLE IF NOT EXISTS v_data_access(
                tenant_id integer,
                company_id integer,
                role_id integer,
                menu_code character(8),
                data_access_code character(3),
                data_access_value character varying(155),
                privilege character(1)
            )
            SERVER hr_um;
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
        //     DROP FOREIGN TABLE v_data_access;
        // ');
    }
}
