<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewSubordinateListRequestsTravel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            create view v_subordinate_req_trv as
                select worklists.id,
                    worklists.lov_wfty as lov_wfty,
                    worklists.request_id as request_id,
                    worklists.ordinal,
                    worklists.requester_id as requester_id,
                    worklists.approver_id as approver_id,
                    worklists.answer,
                    worklists.is_active as is_active,
                    wfty.val_data as val_data,
                    worklists.sub_type as sub_type,
                    worklists.description as description,
                    worklists.created_at as request_date,
                    travel_requests.depart_date as start_date,
                    travel_requests.return_date as end_date,
                    worklists.tenant_id as tenant_id,
                    worklists.company_id as company_id
                from worklists 
                left join lovs as wfty on wfty.key_data = worklists.lov_wfty and
                wfty.tenant_id = worklists.tenant_id and 
                wfty.company_id = worklists.company_id and
                worklists.lov_wfty = 'TRV'
                left join travel_requests on 
                travel_requests.id = worklists.request_id and
                travel_requests.tenant_id = worklists.tenant_id and 
                travel_requests.company_id = worklists.company_id
                where wfty.lov_type_code = 'WFTY'
            ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('drop view v_subordinate_req_trv;');
    }
}
