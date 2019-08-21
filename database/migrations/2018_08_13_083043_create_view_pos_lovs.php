<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewPosLovs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            create view v_pos_lovs as
            select distinct
			    pda.tenant_id,
			    pda.company_id,
			    pda.user_id,
			    pda.menu_code,
			    p.code as position_code,
			    p.name as position_name
			from pos_data_access pda, positions p
			where
			    pda.tenant_id = p.tenant_id and
			    (pda.company_id = p.company_id or pda.company_id IS NULL) and
			    ((
			    	(pda.privilege = 'A') and (p.code like pda.data_access_value)
			    )
			    or
			    (
			    	(pda.privilege != 'A') and (p.code not like pda.data_access_value)
			    ))
                and (now() between p.eff_begin and p.eff_end);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('drop view v_pos_lovs;');
    }
}
