<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignTableRequestRawTimesheets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FOREIGN TABLE IF NOT EXISTS request_raw_timesheets(
                tenant_id integer,
                company_id integer,
                id integer,
                date date,
                -- project_code character varying(20),
                employee_id character varying(20)
            )
            SERVER hr_time;
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
            DROP FOREIGN TABLE request_raw_timesheets;
        ');
    }
}
