<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomFieldPersonBasicInfoDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get one Custom Field Person Basic Info for one person
     * @param $personId
     */
    public function getOne($personId)
    {
        return
            DB::table('cf_person_basic_info')
                ->select(
                    'cf_person_basic_info.id',
                    'cf_person_basic_info.person_id as personId',
                    'cf_person_basic_info.eff_begin as effBegin',
                    'cf_person_basic_info.eff_end as effEnd',
                    'cf_person_basic_info.c1',
                    'cf_person_basic_info.c2',
                    'cf_person_basic_info.c3',
                    'cf_person_basic_info.c4',
                    'cf_person_basic_info.c5',
                    'cf_person_basic_info.c6',
                    'cf_person_basic_info.c7',
                    'cf_person_basic_info.c8',
                    'cf_person_basic_info.c9',
                    'cf_person_basic_info.c10'
                )
                ->where([
                    ['cf_person_basic_info.tenant_id', $this->requester->getTenantId()],
                    ['cf_person_basic_info.company_id', $this->requester->getCompanyId()],
                    ['cf_person_basic_info.person_id',$personId]
                ])
                ->orderBy('cf_person_basic_info.eff_end', 'DESC')
                ->first();
    }

    /**
     * Insert data custom field person basic info to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('cf_person_basic_info')->insertGetId($obj);
    }

    /**
     * Update data custom field person basic info to DB
     * @param personId , array obj
     */
    public function update($personId,$effBegin, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('cf_person_basic_info')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['eff_begin', $effBegin],
                ['person_id', $personId]
            ])
            ->update($obj);
    }

    /**
     * Delete data custom field person basic info from DB
     * @param  personId, effBegin, effEnd
     */
    public function delete($personId, $effBegin, $effEnd)
    {
        DB::table('cf_person_basic_info')->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['eff_begin', $effBegin],
            ['eff_end', $effEnd],
            ['person_id', $personId]
        ])->delete();
    }
}
