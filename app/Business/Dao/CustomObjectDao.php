<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property Requester requester
 */
class CustomObjectDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function getAll()
    {
        return
            DB::table('co')
                ->select(
                    'co.id',
                    'lovs.val_data as moduleName',
                    'co.name',
                    'co.description',
                    'co.is_disabled as isDisabled'
                )
                ->join('lovs', function ($join) {
                    $join->on('lovs.tenant_id', '=', 'co.tenant_id');
                    $join->on('lovs.company_id', '=', 'co.company_id');
                    $join->on('lovs.key_data', '=', 'co.lov_cusobj');
                })
                ->where([
                    ['co.tenant_id', $this->requester->getTenantId()],
                    ['co.company_id', $this->requester->getCompanyId()],
                    ['lovs.lov_type_code', 'CUSOBJ']
                ])
                ->get();
    }

    public function getAllByLovCusobj($lovCusobj)
    {
        return
            DB::table('co')
                ->select(
                    'co.id',
                    'co.name',
                    'co.is_disabled as isDisabled'
                )
                ->where([
                    ['co.tenant_id', $this->requester->getTenantId()],
                    ['co.company_id', $this->requester->getCompanyId()],
                    ['co.lov_cusobj', $lovCusobj]
                ])
                ->get();
    }

    public function getOne($id)
    {
        return
            DB::table('co')
                ->select(
                    'lovs.val_data as moduleName',
                    'co.name',
                    'co.description',
                    'co.lov_cusobj as lovCusobj',
                    'co.is_disabled as isDisabled'
                )
                ->join('lovs', function ($join) {
                    $join->on('lovs.tenant_id', '=', 'co.tenant_id');
                    $join->on('lovs.company_id', '=', 'co.company_id');
                    $join->on('lovs.key_data', '=', 'co.lov_cusobj');
                })
                ->where([
                    ['co.tenant_id', $this->requester->getTenantId()],
                    ['co.company_id', $this->requester->getCompanyId()],
                    ['co.id', $id],
                    ['lovs.lov_type_code', 'CUSOBJ']
                ])
                ->first();
    }

    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'co', $obj);

        return DB::table('co')->insertGetId($obj);
    }

    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'co', $obj);

        DB::table('co')->where('id', $id)->update($obj);
    }

    public function delete($id)
    {
        DB::table('co')->where('id', $id)->delete();
    }
}
