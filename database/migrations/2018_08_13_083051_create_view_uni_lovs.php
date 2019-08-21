<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewUniLovs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            create view v_uni_lovs as
            select distinct
			    uda.tenant_id,
			    uda.company_id,
			    uda.user_id,
			    uda.menu_code,
			    u.code as unit_code,
			    u.name as unit_name
			from uni_data_access uda, units u
			where
			    uda.tenant_id = u.tenant_id and
			    (uda.company_id = u.company_id or uda.company_id IS NULL) and
			    ((
			    	(uda.privilege = 'A') and (u.code like uda.data_access_value)
			    )
			    or
			    (
			    	(uda.privilege != 'A') and (u.code not like uda.data_access_value)
			    ))
                and (now() between u.eff_begin and u.eff_end);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('drop view v_uni_lovs;');
    }
}
