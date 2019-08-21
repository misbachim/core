<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property Requester requester
 */
class PersonCustomObjectDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * @param $personId
     * @return mixed
     */
    public function getAll($personId)
    {
        $now = Carbon::now();
        return
            DB::table('person_co')
                ->select(
                    'person_co.id',
                    'person_co.co_id as coId',
                    'co.name',
                    'person_co.eff_begin as effBegin',
                    'person_co.eff_end as effEnd'
                )
                ->join('co', 'co.id', 'person_co.co_id')
                ->where([
                    ['person_co.tenant_id', $this->requester->getTenantId()],
                    ['person_co.company_id', $this->requester->getCompanyId()],
                    ['person_co.person_id', $personId],
                    ['person_co.eff_begin', '<=', $now],
                    ['person_co.eff_end', '>=', $now],
                    ['co.is_disabled', false]
                ])
                ->get();
    }

    public function getAllItems($personId, $coId)
    {
        $now = Carbon::now();
        return
            DB::table('person_co')
                ->select(
                    'person_co.id',
                    'person_co.eff_begin as effBegin',
                    'person_co.eff_end as effEnd'
                )
                ->join('co', 'co.id', 'person_co.co_id')
                ->where([
                    ['person_co.tenant_id', $this->requester->getTenantId()],
                    ['person_co.company_id', $this->requester->getCompanyId()],
                    ['person_co.person_id', $personId],
                    ['person_co.co_id', $coId],
                    ['person_co.eff_begin', '<=', $now],
                    ['person_co.eff_end', '>=', $now],
                    ['co.is_disabled', false]
                ])
                ->get();
    }

    public function getOne($personId, $coId, $id)
    {
        return
            DB::table('person_co')
                ->select(
                    'co_id as coId',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['person_id', $personId],
                    ['co_id', $coId],
                    ['id', $id]
                ])
                ->get();
    }

    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_co')->insertGetId($obj);
    }

    public function update($personId, $coId, $id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_co')->where([
            ['person_id', $personId],
            ['co_id', $coId],
            ['id', $id]
        ])->update($obj);
    }

    public function delete($personId, $coId, $id)
    {
        DB::table('person_co')->where([
            ['person_id', $personId],
            ['co_id', $coId],
            ['id', $id]
        ])->delete();
    }
}
