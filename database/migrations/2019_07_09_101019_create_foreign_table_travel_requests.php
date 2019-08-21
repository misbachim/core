<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignTableTravelRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FOREIGN TABLE IF NOT EXISTS travel_requests(
                tenant_id integer,
                company_id integer,
                id integer,
                depart_date date,
                return_date date,
                employee_id character varying(20)
            )
            SERVER hr_travel;
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
            DROP FOREIGN TABLE travel_requests;
        ');
    }
}
