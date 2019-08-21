<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReadinessLevelDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
        $this->table = 'readiness_levels';
    }


    /*
    |-----------------------------
    | get all  data dari database
    |-----------------------------
    | @param $companyId <integer>
    |
    */
    public function getAll($companyId)
    {
        $tenantId = $this->requester->getTenantId();
        return
        DB::table('readiness_levels')
        ->select(
            'id',
            'name',
            'description',
            'color',
            'ready_now as readyNow',
            'eff_begin as effBegin',
            'eff_end as effEnd'
        )
        ->where([
            ['readiness_levels.tenant_id', $tenantId],
            ['readiness_levels.company_id', $companyId]
        ])
        ->orderBy('id', 'DESC')
        ->get();
    }

    public function getOne($companyId, $id)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('readiness_levels')
                ->select(
                    'id',
                    'name',
                    'description',
                    'color',
                    'ready_now as readyNow',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['readiness_levels.tenant_id', $tenantId],
                    ['readiness_levels.company_id', $companyId],
                    ['readiness_levels.id', $id]
                ])
                ->orderBy('id', 'DESC')
                ->first();
    }


    /*
    |-----------------------------
    | get one active
    |-----------------------------
    |
    |
    */
    public function getOneActive () {
        return
        DB::table($this->table)
            ->select(
                'id',
                'name',
                'description',
                'color',
                'ready_now as readyNow',
                'eff_begin as effBegin',
                'eff_end as effEnd'
            )
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['eff_begin', '<=', Carbon::now()],
            ['eff_end', '>=', Carbon::now()]
         ])
         ->first();
    }


    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    | @param obj dari controller
    |
    */
    public function save ($obj) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        DB::table($this->table)->insert($obj);
    }


    /*
    |-----------------------------
    | update data ke database
    |-----------------------------
    |
    |
    */
    public function update ($obj, $id) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        DB::table($this->table)
        ->where([
            ['id', $id],
         ])
         ->update($obj);
    }


    /*
    |------------
    | get lov
    |------------
    |
    |
    */
    public function getLov() {
        return
        DB::table($this->table)
            ->select(
                'id',
                'name'
            )
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()]
            ])
         ->get();
    }

}
