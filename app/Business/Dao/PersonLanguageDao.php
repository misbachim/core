<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonLanguageDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person language for one person
     * @param $personId
     */
    public function getAll($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_languages')
                ->select(
                    'person_languages.id',
                    'languages.val_data as language',
                    'writing',
                    'speaking',
                    'listening'
                )
                ->leftJoin('lovs as languages',  function ($join) use($companyId, $tenantId)  {
                    $join->on('languages.key_data', '=', 'person_languages.lov_lang')
                        ->where([
                            ['languages.lov_type_code', 'LANG'],
                            ['languages.tenant_id', $tenantId],
                            ['languages.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_languages.tenant_id', $tenantId],
                    ['person_languages.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person language based on personLanguageId
     * @param $personId
     * @param $personLanguageId
     * @return
     */
    public function getOne($personId, $personLanguageId)
    {
        return
            DB::table('person_languages')
                ->select(
                    'id',
                    'lov_lang as lovLang',
                    'writing',
                    'speaking',
                    'listening',
                    'is_native as isNative'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['id', $personLanguageId]
                ])
                ->first();
    }

    /**
     * Insert data person language to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_languages')->insertGetId($obj);
    }

    /**
     * Update data person language to DB
     * @param $personId
     * @param $personLanguageId
     * @param $obj
     */
    public function update($personId, $personLanguageId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_languages')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personLanguageId]
        ])
        ->update($obj);
    }

    /**
     * Delete data person language from DB.
     * @param $personId
     * @param $personLanguageId
     */
    public function delete($personId, $personLanguageId)
    {
        DB::table('person_languages')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personLanguageId]
        ])
        ->delete();
    }
}
