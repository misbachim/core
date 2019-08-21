<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFJobLovsFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE OR REPLACE FUNCTION f_job_lovs(f_tenant_id integer, f_company_id integer, f_menu_code text, f_role_ids integer[])
            RETURNS TABLE(
                tenant_id INT, 
                company_id INT,
                role_id INT,
                menu_code character(5),
                job_code VARCHAR(20),
                job_name VARCHAR(50)
            ) AS $BODY$
            BEGIN 
                RETURN QUERY
                    SELECT DISTINCT
                        vda.tenant_id,
                        vda.company_id,
                        vda.role_id,
                        vda.menu_code,
                        j.code AS job_code,
                        j.name AS job_name
                    FROM v_data_access vda
					JOIN jobs j
						ON j.tenant_id = f_tenant_id 
                        AND (j.company_id = f_company_id OR j.company_id IS NULL)
                        AND now() >= j.eff_begin AND now() <= j.eff_end
						AND (vda.privilege = \'A\' AND j.code LIKE vda.data_access_value
							 OR vda.privilege != \'A\'
								AND j.code NOT IN(SELECT j.code
														FROM v_data_access vdaz 
														JOIN jobs j
															ON j.code LIKE vdaz.data_access_value
															AND j.tenant_id = f_tenant_id 
															AND (j.company_id = f_company_id OR j.company_id IS NULL)
                                                            AND now() >= j.eff_begin AND now() <= j.eff_end
														WHERE  vdaz.tenant_id = f_tenant_id 
															AND ( vdaz.company_id = f_company_id OR vdaz.company_id IS NULL ) 
														   	AND vdaz.data_access_code = \'JOB\' 
														   	AND vdaz.menu_code = f_menu_code 
														   	AND vdaz.privilege != \'A\'
														   	AND vdaz.role_id = ANY (f_role_ids)))
						
                    WHERE vda.tenant_id = f_tenant_id 
                        AND (vda.company_id = f_company_id OR vda.company_id IS NULL)
                        AND vda.data_access_code = \'JOB\'
                        AND vda.menu_code = f_menu_code
						AND vda.role_id = ANY(f_role_ids);
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
            DROP FUNCTION IF EXISTS f_job_lovs(integer,integer,text,integer[]);
        ');
    }
}
