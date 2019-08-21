<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonOrganizationDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person organization for one person
     * @param personId
     */
    public function getAll($personId)
    {
        return
            DB::table('person_organizations')
                ->select(
                    'id',
                    'institution',
                    'year_begin as yearBegin',
                    'year_end as yearEnd',
                    'description'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person organization based on personOrganizationId
     * @param $personId
     * @param $personOrganizationId
     * @return
     */
    public function getOne($personId, $personOrganizationId)
    {
        return
            DB::table('person_organizations')
                ->select(
                    'id',
                    'institution',
                    'year_begin as yearBegin',
                    'year_end as yearEnd',
                    'description'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['id', $personOrganizationId]
                ])
                ->first();
    }

    /**
     * Insert data person organization to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_organizations')->insertGetId($obj);
    }

    /**
     * Update data person organization to DB
     * @param $personId
     * @param $personOrganizationId
     * @param $obj
     */
    public function update($personId, $personOrganizationId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_organizations')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personOrganizationId]
        ])
        ->update($obj);
    }

    /**
     * Delete data person organization from DB.
     * @param $personId
     * @param $personOrganizationId
     */
    public function delete($personId, $personOrganizationId)
    {
        DB::table('person_organizations')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personOrganizationId]
        ])
        ->delete();
    }
}
