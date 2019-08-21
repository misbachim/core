<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonReferenceDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person reference for one person
     * @param personId
     */
    public function getAll($personId)
    {
        return
            DB::table('person_references')
                ->select(
                    'id',
                    'name',
                    'relationship',
                    'description',
                    'phone'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person reference based on personReferenceId
     * @param $personId
     * @param $personReferenceId
     * @return
     */
    public function getOne($personId, $personReferenceId)
    {
        return
            DB::table('person_references')
                ->select(
                    'id',
                    'name',
                    'relationship',
                    'description',
                    'phone'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['id', $personReferenceId]
                ])
                ->first();
    }

    /**
     * Insert data person reference to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_references')->insertGetId($obj);
    }

    /**
     * Update data person reference to DB
     * @param $personId
     * @param $personReferenceId
     * @param $obj
     */
    public function update($personId, $personReferenceId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_references')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personReferenceId]
        ])
        ->update($obj);
    }

    /**
     * Delete data person reference from DB.
     * @param $personId
     * @param $personReferenceId
     */
    public function delete($personId, $personReferenceId)
    {
        DB::table('person_references')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personReferenceId]
        ])
        ->delete();
    }
}
