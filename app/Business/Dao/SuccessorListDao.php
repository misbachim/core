<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuccessorListDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /*
    |-----------------------------
    | get all  data from database
    |-----------------------------
    | @param $companyId <integer>
    |
    */
    public function getAll($companyId, $successionPoolId)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('successor_lists')
            ->select(
                'id',
                'succession_pool_id as successionPoolId',
                'employee_id as employeeId',
                'readiness_id as readinessId',
                'note'
            )
            ->where([
                ['succession_pool_id', $successionPoolId],
                ['tenant_id', $tenantId],
                ['company_id', $companyId]
            ])
            ->orderByRaw('id DESC')
            ->get();
    }


    /*
    |-----------------------------
    | get one data from database
    |-----------------------------
    |
    */
    public function getOne ($id)
    {
        return
            DB::table('successor_lists')
                ->select(
                    'id',
                    'succession_pool_id as successionPoolId',
                    'employee_id as employeeId',
                    'readiness_id as readinessId',
                    'note'
                )
                ->where([
                    ['id', $id]
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

        DB::table('successor_lists')->insert($obj);
    }


    /*
    |-----------------------------
    | update data ke database
    |-----------------------------
    |
    |
    */
    public function update ($obj, $id) {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('successor_lists')
        ->where([
            ['id', $id],
         ])
         ->update($obj);
    }

    public function delete ($id) {
        DB::table('successor_lists')
            ->where([
                ['id', $id]
            ])
            ->delete();
    }

}
