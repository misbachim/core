<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignTableLeaveRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FOREIGN TABLE IF NOT EXISTS leave_requests(
                tenant_id integer,
                company_id integer,
                id integer,
                -- leave_code character varying(20),
                -- description character varying(255),
                -- status char(1),
                -- created_by integer,
                -- created_at timestamp with time zone,
                -- updated_by integer,
                -- updated_at timestamp with time zone,
                -- file_reference character varying(1000),
                employee_id character varying(500)
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
            DROP FOREIGN TABLE leave_requests;
        ');
    }
}
