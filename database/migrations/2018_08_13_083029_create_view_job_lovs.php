<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewJobLovs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            create view v_job_lovs as
            select distinct
			    jda.tenant_id,
			    jda.company_id,
			    jda.user_id,
			    jda.menu_code,
			    j.code as job_code,
			    j.name as job_name
			from job_data_access jda, jobs j
			where
			    jda.tenant_id = j.tenant_id and
			    (jda.company_id = j.company_id or jda.company_id IS NULL) and
			    ((
			    	(jda.privilege = 'A') and (j.code like jda.data_access_value)
			    )
			    or
			    (
			    	(jda.privilege != 'A') and (j.code not like jda.data_access_value)
			    ))
                and (now() between j.eff_begin and j.eff_end);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('drop view v_job_lovs;');
    }
}
