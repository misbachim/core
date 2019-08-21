<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFPosLovsFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE OR REPLACE FUNCTION f_pos_lovs(f_tenant_id integer, f_company_id integer, f_menu_code text, f_role_ids integer[])
            RETURNS TABLE(
                tenant_id INT, 
                company_id INT,
                role_id INT,
                menu_code character(5),
                position_code VARCHAR(20),
                position_name VARCHAR(50)
            ) AS $BODY$
            BEGIN 
                RETURN QUERY
                    SELECT DISTINCT
                        vda.tenant_id,
                        vda.company_id,
                        vda.role_id,
                        vda.menu_code,
                        p.code AS position_code,
                        p.name AS position_name
                    FROM v_data_access vda
					JOIN positions p
						ON p.tenant_id = f_tenant_id 
                        AND (p.company_id = f_company_id OR p.company_id IS NULL)
                        AND now() >= p.eff_begin AND now() <= p.eff_end
						AND (vda.privilege = \'A\' AND p.code LIKE vda.data_access_value
							 OR vda.privilege != \'A\'
								AND p.code NOT IN(SELECT p.code
														FROM v_data_access vdaz 
														JOIN positions p
															ON p.code LIKE vdaz.data_access_value
															AND p.tenant_id = f_tenant_id 
															AND (p.company_id = f_company_id OR p.company_id IS NULL)
                                                            AND now() >= p.eff_begin AND now() <= p.eff_end
														WHERE  vdaz.tenant_id = f_tenant_id 
															AND ( vdaz.company_id = f_company_id OR vdaz.company_id IS NULL ) 
														   	AND vdaz.data_access_code = \'POS\' 
														   	AND vdaz.menu_code = f_menu_code 
														   	AND vdaz.privilege != \'A\'
														   	AND vdaz.role_id = ANY (f_role_ids)))
						
                    WHERE vda.tenant_id = f_tenant_id 
                        AND (vda.company_id = f_company_id OR vda.company_id IS NULL)
                        AND vda.data_access_code = \'POS\'
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
            DROP FUNCTION IF EXISTS f_pos_lovs(integer,integer,text,integer[]);
        ');
    }
}
