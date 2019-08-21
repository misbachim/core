<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CostCenterDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all cost center in one company
     * @param  companyId, offset, limit
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('cost_centers')
                ->select(
                    'code',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'name'
                )
                ->where([
                    ['company_id',$this->requester->getCompanyId()],
                    ['tenant_id',$this->requester->getTenantId()]
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * @param
     * @return
     */
    public function getAllActive($offset, $limit){
        return
            DB::table('cost_centers')
                ->select(
                    'code',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'name'
                )
                ->where([
                    ['company_id',$this->requester->getCompanyId()],
                    ['tenant_id',$this->requester->getTenantId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()],
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    public function getAllInActive($offset, $limit){
        return
            DB::table('cost_centers')
                ->select(
                    'code',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'name'
                )
                ->where([
                    ['company_id',$this->requester->getCompanyId()],
                    ['tenant_id',$this->requester->getTenantId()],
                    ['eff_end', '<', Carbon::now()],
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }


    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('cost_centers')
            ->where([
                ['tenant_id',$this->requester->getTenantId()],
                ['company_id',$this->requester->getCompanyId()]
            ])
            ->count();
    }


    /**
     * Get one cost center based on cost center code
     * @param code
     */
    public function getOne($code)
    {
        return
            DB::table('cost_centers')
                ->select(
                    'code',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'name'
                )
                ->where([
                    ['tenant_id',$this->requester->getTenantId()],
                    ['company_id',$this->requester->getCompanyId()],
                    ['code', $code]
                ])
                ->first();
    }

    public function getDefault($positionCode)
    {
        return
            DB::table('cost_centers')
                ->select('cost_centers.code', 'cost_centers.name')
                ->join('positions', 'positions.cost_center_code', '=', 'cost_centers.code')
                ->where([
                    ['cost_centers.tenant_id', $this->requester->getTenantId()],
                    ['cost_centers.company_id', $this->requester->getCompanyId()],
                    ['positions.code', $positionCode]
                ])
                ->first();
    }

    /**
     * Get all costCenter in ONE company
     * @param
     */
    public function getLov()
    {
        return
            DB::table('cost_centers')
                ->select(
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Insert data cost center to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'cost_centers', $obj);

        DB::table('cost_centers')->insert($obj);
    }

    /**
     * Update data cost center to DB
     * @param  code, array obj
     */
    public function update($code, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'cost_centers', $obj);

        DB::table('cost_centers')
        ->where([
            ['tenant_id',$this->requester->getTenantId()],
            ['company_id',$this->requester->getCompanyId()],
            ['code', $code]
        ])
        ->update($obj);
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateCostCenCode(string $code)
    {
        return DB::table('cost_centers')->where([
            ['code', $code],
            ['tenant_id',$this->requester->getTenantId()],
            ['company_id',$this->requester->getCompanyId()]
        ])->count();
    }


    /**
     * @param string name
     * @param $code if update data, then check duplicate name beside current cost center code
     * @return
     */
    public function checkDuplicateEditCostCenName(string $name,string $code)
    {
        $result = DB::table('cost_centers')->where([
            ['name', $name],
            ['tenant_id',$this->requester->getTenantId()],
            ['company_id',$this->requester->getCompanyId()]
        ]);

        if (!is_null($code)) {
            $result->where('code', '!=', $code);
        }

        return $result->count();
    }

    /**
     * @param string name
     * @return
     */
    public function checkDuplicateCostCenName(string $name)
    {
        return DB::table('cost_centers')->where([
            ['name', $name],
            ['company_id',$this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }


    /**
     * Delete data costCenter from DB
     * @param  code
     */
    public function delete($code)
    {
        DB::table('cost_centers')->where([
            ['code', $code],
            ['company_id',$this->requester->getCompanyId()]
        ])->delete();
    }


}
