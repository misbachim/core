<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonRewardDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all personReward for one person
     * @param $companyId
     * @param $personId
     * @return
     */
    public function getAll($companyId, $personId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('person_rewards')
                ->select(
                    'person_rewards.id',
                    'person_rewards.eff_begin as effBegin',
                    'person_rewards.eff_end as effEnd',
                    'rewards.name as name',
                    'rewards.lov_rwty as rewardType',
                    'reward_types.val_data as type',
                    'person_rewards.description'
                )
                ->join('rewards', 'person_rewards.reward_code', '=', 'rewards.code')
                ->join('lovs as reward_types',  function ($join) use($companyId, $tenantId)  {
                    $join->on('reward_types.key_data', '=', 'rewards.lov_rwty')
                        ->where([
                            ['reward_types.lov_type_code', 'RWTY'],
                            ['reward_types.tenant_id', $tenantId],
                            ['reward_types.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_rewards.tenant_id', $tenantId],
                    ['person_rewards.company_id', $companyId],
                    ['person_rewards.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one personReward based on personRewardId
     * @param $companyId
     * @param $personId
     * @param $personRewardId
     * @return
     */
    public function getOne($companyId, $personId, $personRewardId)
    {
        return
            DB::table('person_rewards')
                ->select(
                    'person_rewards.id',
                    'person_rewards.eff_begin as effBegin',
                    'person_rewards.eff_end as effEnd',
                    'rewards.code as rewardCode',
                    'rewards.name as rewardName',
                    'person_rewards.description as description',
                    'rewards.lov_rwty as lovRwty'
                )
                ->join('rewards', 'person_rewards.reward_code', '=', 'rewards.code')
                ->where([
                    ['person_rewards.tenant_id', $this->requester->getTenantId()],
                    ['person_rewards.company_id', $companyId],
                    ['person_rewards.person_id', $personId],
                    ['person_rewards.id', $personRewardId]
                ])
                ->first();
    }

    /**
     * Insert data personReward to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_rewards')->insertGetId($obj);
    }

    /**
     * Update data personReward to DB
     * @param $companyId
     * @param $personId
     * @param $personRewardId
     * @param $obj
     */
    public function update($companyId, $personId, $personRewardId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_rewards')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['person_id', $personId],
            ['id', $personRewardId]
        ])
        ->update($obj);
    }

    /**
     * Delete data personReward from DB.
     * @param $companyId
     * @param $personId
     * @param $personRewardId
     * @internal param $tenantId , companyId, personId, personRewardId
     */
    public function delete($companyId, $personId, $personRewardId)
    {
        DB::table('person_rewards')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['person_id', $personId],
            ['id', $personRewardId]
        ])
        ->delete();
    }
}
