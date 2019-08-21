<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestFamiliesDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all request person family for one person
     * @param  tenantId, personId
     */
    public function getAll($employeeId, $companyId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('request_families')
                ->selectRaw(
                    'request_families.id,'.
                    'request_families.employee_id,'.
                    'request_families.person_id,'.
                    'request_families.name,'.
                    'request_families.birth_date as "birthDate",'.
                    'request_families.is_emergency as "isEmergency",'.
                    'relationships.val_data as "relationship",'.
                    'age(request_families.birth_date) as "age",'.
                    'educations.val_data as "education",'.
                    'request_families.occupation,'.
                    'request_families.is_emergency as "isEmergency"'
                )
                ->leftJoin('lovs as relationships',  function ($join) use($companyId, $tenantId)  {
                    $join->on('relationships.key_data', '=', 'request_families.lov_famr')
                        ->where([
                            ['relationships.lov_type_code', 'FAMR'],
                            ['relationships.tenant_id', $tenantId],
                            ['relationships.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as educations', function ($join) use($companyId, $tenantId)  {
                    $join->on('educations.key_data', '=', 'request_families.lov_edul')
                        ->where([
                            ['educations.lov_type_code', 'EDUL'],
                            ['educations.tenant_id', $tenantId],
                            ['educations.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['request_families.tenant_id', $tenantId],
                    ['request_families.employee_id', $employeeId]
                ])
                ->get();
    }

    public function checkIfRequestIsPending($employeeId, $status){
        return
            DB::table('request_families')
                ->select(
                    'id',
                    'employee_id',
                    'person_id',
                    'person_family_id as personFamilyId',
                    'crud_type as crudType',
                    'status'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['employee_id', $employeeId],
                    ['status', $status]
                ])
                ->get();
    }

    /**
     * Get one request person family based on personFamilyId
     * @param $personId
     * @param $personFamilyId
     * @return
     */
    public function getOne($personFamilyId)
    {
        return
            DB::table('request_families')
                ->select(
                    'id',
                    'company_id as companyId',
                    'employee_id',
                    'person_id',
                    'name',
                    'person_family_id as personFamilyId',
                    'crud_type as crudType',
                    'lov_famr as lovFamr',
                    'lov_gndr as lovGndr',
                    'lov_edul as lovEdul',
                    'birth_date as birthDate',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'occupation',
                    'is_emergency as isEmergency',
                    'phone as phone',
                    'description',
                    'request_date as requestDate',
                    'status'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['id', $personFamilyId]
                ])
                ->first();
    }

    /**
     * Insert data Person Families Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('request_families')->insertGetId($obj);
    }

    /**
     * Update data Person Families Request to DB
     * @param  array obj, personId, personFamilyId
     */
    public function update($personId, $personFamilyId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('request_families')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId],
                ['id', $personFamilyId]
            ])
            ->update($obj);
    }

    /**
     * Delete data person family Request from DB.
     * @param $personId
     * @param $personFamilyId
     */
    public function delete($personFamilyId)
    {
        DB::table('request_families')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personFamilyId]
            ])
            ->delete();
    }

}
