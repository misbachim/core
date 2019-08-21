<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class TalentPoolDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
        $this->table = 'talent_pools';
    }


    /*
    |-----------------------------
    | get all data ke database
    |-----------------------------
    |
    |
    */
    public function getAll() {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return DB::table($this->table)
            ->select(
                'id',
                'name',
                'description',
                'bench_strength as benchStrength',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'automatic',
                'created_by as createdBy'
            )
        ->where([
            ['company_id', $companyId],
            ['tenant_id', $tenantId],
         ])
         ->orderBy('id', 'DESC')
         ->get();
    }


     /*
    |-----------------------------
    | get one data ke database
    |-----------------------------
    |
    |
    */
    public function getOne($id) {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return DB::table($this->table)
            ->select(
                'id',
                'name',
                'description',
                'bench_strength as benchStrength',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'automatic',
                'created_by as createdBy'
            )
        ->where([
            ['company_id', $companyId],
            ['tenant_id', $tenantId],
            ['id', $id],
         ])
         ->first();
    }


    /*
    |-----------------------------
    | update data ke database
    |-----------------------------
    |
    |
    */
    public function update($obj, $id) {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();
        DB::table($this->table)
        ->where([
            ['id', $id],
         ])
         ->update($obj);
    }




    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    |
    | @param obj dari controller
    */
    public function save($obj) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        $obj['tenant_id'] = $this->requester->getTenantId();
        $obj['company_id'] = $this->requester->getCompanyId();
        DB::table($this->table)->insert($obj);
    }

}
