<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class RewardDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all reward for one company
     * @param  tenantId, companyId
     */
    public function getAll($tenantId, $companyId)
    {
        return
            DB::table('rewards')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'description',
                    'lov_rwty as lovRwty',
                    'lovs.val_data as rwty'
                )
                ->leftJoin('lovs', function ($join) use($companyId, $tenantId)  {
                    $join->on('lovs.key_data', '=', 'rewards.lov_rwty')
                        ->where([
                            ['lovs.lov_type_code', 'RWTY'],
                            ['lovs.tenant_id', $tenantId],
                            ['lovs.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['rewards.tenant_id', $tenantId],
                    ['rewards.company_id', $companyId]
                ])
                ->get();
    }

    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('rewards')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'description',
                    'lov_rwty as lovRwty',
                    'lovs.val_data as rwty'
                )
                ->leftJoin('lovs', function ($join)  {
                    $join->on('lovs.key_data', '=', 'rewards.lov_rwty')
                        ->where([
                            ['lovs.lov_type_code', 'RWTY'],
                            ['lovs.tenant_id', $this->requester->getTenantId()],
                            ['lovs.company_id',$this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['rewards.tenant_id', $this->requester->getTenantId()],
                    ['rewards.company_id', $this->requester->getCompanyId()],
                    ['rewards.eff_begin', '<=', Carbon::now()],
                    ['rewards.eff_end', '>=', Carbon::now()]
            ])
            ->orderByRaw('rewards.eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    public function getAllInActive()
    {
        return 
            DB::table('rewards')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'description',
                    'lov_rwty as lovRwty',
                    'lovs.val_data as rwty'
                )
                ->leftJoin('lovs', function ($join)  {
                    $join->on('lovs.key_data', '=', 'rewards.lov_rwty')
                        ->where([
                            ['lovs.lov_type_code', 'RWTY'],
                            ['lovs.tenant_id', $this->requester->getTenantId()],
                            ['lovs.company_id',$this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['rewards.tenant_id', $this->requester->getTenantId()],
                    ['rewards.company_id', $this->requester->getCompanyId()],
                    ['rewards.eff_end', '<', Carbon::now()]
                ])
            ->get();
    }

    /**
     * Get one reward based on rewardId
     * @param  tenantId, companyId, rewardId
     */
    public function getOne($tenantId, $companyId, $rewardId)
    {
        return
            DB::table('rewards')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'description',
                    'lov_rwty as lovRwty'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['company_id', $companyId],
                    ['id', $rewardId]
                ])
                ->first();
    }

    /**
     * Insert data reward to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'rewards', $obj);

        return DB::table('rewards')->insertGetId($obj);
    }

    /**
     * Update data reward to DB
     * @param  array obj, tenantId, companyId, rewardId
     */
    public function update($tenantId, $companyId, $rewardId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'rewards', $obj);

        DB::table('rewards')
        ->where([
            ['tenant_id', $tenantId],
            ['company_id', $companyId],
            ['id', $rewardId]
        ])
        ->update($obj);
    }

    /**
     * Delete data reward from DB.
     * @param tenantId, companyId, rewardId
     */
    public function delete($tenantId, $companyId, $rewardId)
    {
        DB::table('rewards')
        ->where([
            ['tenant_id', $tenantId],
            ['company_id', $companyId],
            ['id', $rewardId]
        ])
        ->delete();
    }

    public function checkDuplicateCode(string $code)
    {
        return (DB::table('rewards')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

    public function getTotalRows()
    {
        return
            DB::table('rewards')
            ->leftJoin('lovs', 'lovs.key_data', '=', 'rewards.lov_rwty')
            ->where([
                ['rewards.tenant_id', $this->requester->getTenantId()],
                ['rewards.company_id', $this->requester->getCompanyId()]
            ])->count();
    }
}
