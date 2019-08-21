<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFPersonLovsFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE OR REPLACE FUNCTION f_person_lovs(f_tenant_id integer, f_company_id integer, f_menu_code text, f_role_ids integer[])
            RETURNS TABLE(
                tenant_id INT, 
                company_id INT,
                role_id INT,
                menu_code character(5),
                job_code VARCHAR(20),
                position_code VARCHAR(20),
                unit_code VARCHAR(20),
                person_id INT,
                first_name VARCHAR(50),
                last_name VARCHAR(50)
            ) AS $BODY$
            BEGIN 
                RETURN QUERY
                    SELECT DISTINCT
                        p.tenant_id, 
                        a.company_id, 
                        fjl.role_id,
                        fjl.menu_code,
                        fjl.job_code,
                        fpl.position_code,
                        ful.unit_code,
                        p.id, 
                        p.first_name, 
                        p.last_name
                    FROM persons p
                    JOIN 
                        (SELECT * FROM assignments a WHERE a.tenant_id=f_tenant_id and a.company_id=f_company_id)
                        a ON a.person_id = p.id 
                            AND a.tenant_id = p.tenant_id 
                            AND a.is_primary = TRUE
                            AND a.eff_begin <= now() AND a.eff_end >= now() 
                            AND p.eff_begin <= now() AND p.eff_end >= now()
                    JOIN 
                        f_job_lovs(f_tenant_id, f_company_id, f_menu_code, f_role_ids) fjl 
                        ON fjl.job_code = a.job_code
                    JOIN 
                        f_uni_lovs(f_tenant_id, f_company_id, f_menu_code, f_role_ids) ful 
                        ON ful.unit_code = a.unit_code
                    JOIN 
                        f_pos_lovs(f_tenant_id, f_company_id, f_menu_code, f_role_ids) fpl 
                        ON fpl.position_code = a.position_code
                    WHERE
                        p.tenant_id=f_tenant_id;
            END;
            $BODY$ LANGUAGE \'plpgsql\';
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
            DROP FUNCTION IF EXISTS f_person_lovs(integer,integer,text,integer[]);
        ');
    }
}
