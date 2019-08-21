<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignTableLoanRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FOREIGN TABLE IF NOT EXISTS loan_requests(
                tenant_id integer,
                company_id integer,
                id integer,
                created_at date,
                employee_id character varying(20)
            )
            SERVER hr_payroll;
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
            DROP FOREIGN TABLE loan_requests;
        ');
    }
}
