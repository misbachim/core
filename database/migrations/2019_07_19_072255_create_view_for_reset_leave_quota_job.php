<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewForResetLeaveQuotaJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE VIEW v_for_reset_leave_quota_job AS
SELECT DISTINCT assignments.tenant_id AS tenant_id
              , assignments.company_id AS company_id
              , assignments.employee_id AS employee_id
FROM persons
INNER JOIN assignments
ON persons.id = assignments.person_id
INNER JOIN companies
ON companies.id = assignments.company_id
INNER JOIN v_active_tenant_ids
ON v_active_tenant_ids.id = companies.tenant_id
WHERE
NOW() BETWEEN assignments.eff_begin AND assignments.eff_end
AND
assignments.lov_acty != 'TERM'
;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS v_for_reset_leave_quota_job;");
    }
}
