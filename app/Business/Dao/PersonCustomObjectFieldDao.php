<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonCustomObjectFieldDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function getAll($personCustomObjectId)
    {
        return
            DB::table('person_co_fields')
            ->select(
                'co_field_id as coFieldId',
                'name as coFieldName',
                'value',
                'lov_cdtype as lovCdtype',
                'lov_type_code as lovTypeCode'
            )
            ->leftJoin('co_fields', 'co_fields.id', 'co_field_id')
            ->where([
                ['person_co_fields.tenant_id', $this->requester->getTenantId()],
                ['person_co_fields.company_id', $this->requester->getCompanyId()],
                ['person_co_id', $personCustomObjectId],
                ['is_disabled', false]
            ])
            ->get();
    }

    public function saveAll($objects)
    {
        DB::table('person_co_fields')->insert($objects);
    }

    public function upsert($personCoId, $coFieldId, $value)
    {
        DB::table('person_co_fields')->updateOrInsert([
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'person_co_id' => $personCoId,
            'co_field_id' => $coFieldId
        ], [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'person_co_id' => $personCoId,
            'co_field_id' => $coFieldId,
            'value' => $value
        ]);
    }

    public function getValue($personId, $coFieldId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();

        return
            DB::table('person_co_fields')
            ->select(
                'person_co_fields.value',
                'co_fields.name',
                'co_fields.lov_type_code as lovTypeCode'
            )
            ->join('person_co', function ($join) use ($companyId, $tenantId, $now, $personId) {
                $join->on('person_co.id', '=', 'person_co_fields.person_co_id')
                    ->where([
                        ['person_co.tenant_id', $tenantId],
                        ['person_co.company_id', $companyId],
                        ['person_co.eff_begin', '<=', $now],
                        ['person_co.eff_end', '>=', $now],
                        ['person_co.person_id', $personId]
                    ]);
            })
            ->join('co_fields', function ($join) use ($companyId, $tenantId, $personId) {
                $join->on('co_fields.id', '=', 'person_co_fields.co_field_id')
                    ->where([
                        ['co_fields.tenant_id', $tenantId],
                        ['co_fields.company_id', $companyId]
                    ]);
            })
            ->where([
                ['person_co_fields.tenant_id', $tenantId],
                ['person_co_fields.company_id', $companyId],
                ['person_co_fields.co_field_id', $coFieldId]
            ])
            ->first();
    }

    public function deleteAll($personCustomObjectId)
    {
        DB::table('person_co_fields')->where('person_co_id', $personCustomObjectId)->delete();
    }
}
