<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewSubordinateListRequestsLeave extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            create view v_subordinate_req_leav as
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
                    min(leave_request_details.date) as start_date,
                    max(leave_request_details.date) as end_date,
                    worklists.tenant_id as tenant_id,
                    worklists.company_id as company_id
                from worklists 
                left join lovs as wfty on wfty.key_data = worklists.lov_wfty and
                wfty.tenant_id = worklists.tenant_id and 
                wfty.company_id = worklists.company_id
                left join leave_requests on 
                leave_requests.id = worklists.request_id and
                leave_requests.tenant_id = worklists.tenant_id and 
                leave_requests.company_id = worklists.company_id
                left join leave_request_details on 
                leave_requests.id = leave_request_details.leave_request_id and
                leave_requests.tenant_id = leave_request_details.tenant_id and 
                leave_requests.company_id = leave_request_details.company_id
                where worklists.lov_wfty = 'LEAV' and wfty.lov_type_code = 'WFTY'
                group by (worklists.id,
                    worklists.lov_wfty,
                    worklists.request_id,
                    worklists.ordinal,
                    worklists.requester_id,
                    worklists.approver_id,
                    worklists.answer,
                    worklists.is_active,
                    wfty.val_data,
                    worklists.sub_type,
                    worklists.description,
                    worklists.created_at,
                    worklists.tenant_id,
                    worklists.company_id
                )
            ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('drop view v_subordinate_req_leav;');
    }
}
