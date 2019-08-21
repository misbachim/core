<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserIdToRoleIdOnAllView extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('drop view v_person_lovs;');
		DB::statement('drop view v_job_lovs;');
		DB::statement('drop view v_pos_lovs;');

		DB::statement("
            create view v_job_lovs as
            select distinct
			    daj.tenant_id,
			    daj.company_id,
			    daj.role_id,
			    daj.menu_code,
			    j.code as job_code,
			    j.name as job_name
			from data_access_job daj, jobs j
			where
			    daj.tenant_id = j.tenant_id and
			    (daj.company_id = j.company_id or daj.company_id IS NULL) and
			    ((
			    	(daj.privilege = 'A') and (j.code like daj.data_access_value)
			    )
			    or
			    (
			    	(daj.privilege != 'A') and (j.code not like daj.data_access_value)
			    ))
                and (now() between j.eff_begin and j.eff_end);
        ");

		DB::statement("
            create view v_pos_lovs as
            select distinct
			    dap.tenant_id,
			    dap.company_id,
			    dap.role_id,
			    dap.menu_code,
			    p.code as position_code,
			    p.name as position_name
			from data_access_pos dap, positions p
			where
			    dap.tenant_id = p.tenant_id and
			    (dap.company_id = p.company_id or dap.company_id IS NULL) and
			    ((
			    	(dap.privilege = 'A') and (p.code like dap.data_access_value)
			    )
			    or
			    (
			    	(dap.privilege != 'A') and (p.code not like dap.data_access_value)
			    ))
                and (now() between p.eff_begin and p.eff_end);
        ");

		DB::statement('drop view v_uni_lovs;');
		DB::statement("
            create view v_uni_lovs as
            select distinct
			    dau.tenant_id,
			    dau.company_id,
			    dau.role_id,
			    dau.menu_code,
			    u.code as unit_code,
			    u.name as unit_name
			from data_access_uni dau, units u
			where
			    dau.tenant_id = u.tenant_id and
			    (dau.company_id = u.company_id or dau.company_id IS NULL) and
			    ((
			    	(dau.privilege = 'A') and (u.code like dau.data_access_value)
			    )
			    or
			    (
			    	(dau.privilege != 'A') and (u.code not like dau.data_access_value)
			    ))
                and (now() between u.eff_begin and u.eff_end);
        ");

		DB::statement('
            create view v_person_lovs as
            select
			    p.tenant_id,
			    a.company_id,
			    vjl.role_id,
			    vjl.menu_code,
			    p.id as person_id,
			    p.first_name as person_first_name,
			    p.last_name as person_last_name
			from persons p
			join assignments a on a.person_id = p.id
			AND a.tenant_id = p.tenant_id 
            AND a.eff_begin <= now() AND a.eff_end >= now() 
            AND p.eff_begin <= now() AND p.eff_end >= now()
			join jobs j on a.job_code = j.code
			AND j.tenant_id = a.tenant_id AND j.company_id = a.company_id
	        join v_job_lovs vjl on vjl.job_code = j.code
	        AND vjl.tenant_id = j.tenant_id AND vjl.company_id = j.company_id
			union
			select
			    p.tenant_id,
			    a.company_id,
			    vpsl.role_id,
			    vpsl.menu_code,
			    p.id as person_id,
			    p.first_name as person_first_name,
			    p.last_name as person_last_name
			from persons p
			join assignments a on a.person_id = p.id
			AND a.tenant_id = p.tenant_id 
            AND a.eff_begin <= now() AND a.eff_end >= now() 
            AND p.eff_begin <= now() AND p.eff_end >= now()
			join positions ps on a.position_code = ps.code
			AND ps.tenant_id = a.tenant_id AND ps.company_id = a.company_id
			join v_pos_lovs vpsl on vpsl.position_code = ps.code
			AND vpsl.tenant_id = ps.tenant_id AND vpsl.company_id = ps.company_id
			union
			select
			    p.tenant_id,
			    a.company_id,
			    vul.role_id,
			    vul.menu_code,
			    p.id as person_id,
			    p.first_name as person_first_name,
			    p.last_name as person_last_name
			from persons p
			join assignments a on a.person_id = p.id
			AND a.tenant_id = p.tenant_id 
            AND a.eff_begin <= now() AND a.eff_end >= now() 
            AND p.eff_begin <= now() AND p.eff_end >= now()
			join units u on a.unit_code = u.code
            AND u.tenant_id = a.tenant_id AND u.company_id = a.company_id
            join v_uni_lovs vul on vul.unit_code = u.code
            AND vul.tenant_id = u.tenant_id AND vul.company_id = u.company_id;
        ');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
}
