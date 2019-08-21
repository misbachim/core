<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonMembershipDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person membership for one person
     * @param $companyId
     * @param $personId
     * @return
     */
    public function getAll($companyId, $personId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('person_memberships')
                ->select(
                    'person_memberships.id',
                    'memberships.val_data as membership',
                    'person_memberships.acc_number as accNumber',
                    'person_memberships.employment_code as employmentCode',
                    'person_memberships.eff_begin as effBegin',
                    'person_memberships.eff_end as effEnd'
                )
                ->leftJoin('lovs as memberships', function ($join) use($companyId, $tenantId)  {
                    $join->on('memberships.key_data', '=', 'person_memberships.lov_mbty')
                        ->where([
                            ['memberships.lov_type_code', 'MBTY'],
                            ['memberships.tenant_id', $tenantId],
                            ['memberships.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_memberships.tenant_id', $tenantId],
                    ['person_memberships.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person membership based on personMembershipId
     * @param $companyId
     * @param $personId
     * @param $personMembershipId
     * @return
     */
    public function getOne($companyId, $personId, $personMembershipId)
    {
        return
            DB::table('person_memberships')
                ->select(
                    'id',
                    'lov_mbty as lovMbty',
                    'acc_number as accNumber',
                    'employment_code as employmentCode',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['person_id', $personId],
                    ['id', $personMembershipId]
                ])
                ->first();
    }

    /**
     * Insert data person membership to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_memberships')->insertGetId($obj);
    }

    /**
     * Update data person membership to DB
     * @param $companyId
     * @param $personId
     * @param $personMembershipId
     * @param $obj
     */
    public function update($companyId, $personId, $personMembershipId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_memberships')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['person_id', $personId],
            ['id', $personMembershipId]
        ])
        ->update($obj);
    }

    /**
     * Delete data person membership from DB.
     * @param $personId
     * @param $personMembershipId
     */
    public function delete($companyId, $personId, $personMembershipId)
    {
        DB::table('person_memberships')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['person_id', $personId],
            ['id', $personMembershipId]
        ])
        ->delete();
    }
}
