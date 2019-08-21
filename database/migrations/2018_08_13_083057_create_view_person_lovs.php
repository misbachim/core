<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewPersonLovs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            create view v_person_lovs as
            select
			    p.tenant_id,
			    a.company_id,
			    vjl.user_id,
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
			    vpsl.user_id,
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
			    vul.user_id,
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
        DB::statement('drop view v_person_lovs;');
    }
}
