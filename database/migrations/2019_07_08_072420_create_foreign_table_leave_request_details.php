<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignTableLeaveRequestDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FOREIGN TABLE IF NOT EXISTS leave_request_details(
                tenant_id integer,
                company_id integer,
                leave_request_id integer,
                date date
                -- weight double precision,
                -- status char
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
            DROP FOREIGN TABLE leave_request_details;
        ');
    }
}
