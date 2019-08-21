<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class TalentPoolNomineeDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
        $this->table = 'talent_pool_nominees';
    }


    /*
    |-----------------------------
    | get all data ke database
    |-----------------------------
    |
    |
    */
    public function getAllNomineeByTalentPoolId($talentPoolId) {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return DB::table($this->table)
            ->select(
                'id',
                'employee_id as employeeId',
                'status',
                'readiness',
                'year',
                'note'
            )
        ->where([
            ['company_id', $companyId],
            ['tenant_id', $tenantId],
            ['talent_pool_id', $talentPoolId]

         ])
         ->get();
    }



    public function countNominee(int $talentPoolId)
    {
        return DB::table($this->table)
            ->select('id')
            ->where([
                    ['talent_pool_id', $talentPoolId],
                    ['tenant_id',$this->requester->getTenantId()],
                    ['company_id',$this->requester->getCompanyId()]
                ])
            ->count();
    }


    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    |
    | @param obj dari controller
    */
    public function save($obj) {
        $obj = $this->setDefaultValue($obj);
        DB::table($this->table)->insert($obj);
    }


    /*
    |----------------------------------
    | save data ke database dan get id
    |-----------------------------------
    | @param array obj dari controller
    |
    */
    public function saveGetId($obj) {
        $obj = $this->setDefaultValue($obj);
        return DB::table($this->table)->insertGetId($obj);
    }


    private function setDefaultValue($obj) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        $obj['tenant_id'] = $this->requester->getTenantId();
        $obj['company_id'] = $this->requester->getCompanyId();
        return $obj;
    }


    /*
    |-----------------------------
    | delete data di database
    |-----------------------------
    |
    |
    */
    public function delete($id) {
        return DB::table($this->table)
            ->where([
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $id],
             ])
            ->delete();
    }

    /*
    |-----------------------------
    | update data ke database
    |-----------------------------
    |
    |
    */
    public function update($obj, $id) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        DB::table($this->table)
        ->where([
            ['id', $id],
         ])
         ->update($obj);
    }
}
