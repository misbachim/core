<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestPersonFamiliesDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * @param $profileRequestId
     */
    public function getMany($profileRequestId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('request_person_families')
                ->selectRaw(
                    'request_person_families.id,' .
                    'request_person_families.name,' .
                    'request_person_families.birth_date as "birthDate",' .
                    'relationships.val_data as "relationship",' .
                    'age(request_person_families.birth_date) as "age",' .
                    'educations.val_data as "education",' .
                    'request_person_families.occupation,' .
                    'request_person_families.phone,' .
                    'request_person_families.address,' .
                    'request_person_families.is_emergency as "isEmergency"'
                )
                ->leftJoin('lovs as relationships', function ($join) use ($companyId, $tenantId) {
                    $join->on('relationships.key_data', '=', 'request_person_families.lov_famr')
                        ->where([
                            ['relationships.lov_type_code', 'FAMR'],
                            ['relationships.tenant_id', $tenantId],
                            ['relationships.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as educations', function ($join) use ($companyId, $tenantId) {
                    $join->on('educations.key_data', '=', 'request_person_families.lov_edul')
                        ->where([
                            ['educations.lov_type_code', 'EDUL'],
                            ['educations.tenant_id', $tenantId],
                            ['educations.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['request_person_families.tenant_id', $tenantId],
                    ['request_person_families.profile_request_id', $profileRequestId]
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
            DB::table('request_person_families')
                ->select(
                    'id',
                    'name',
                    'profile_request_id as profileRequestId',
                    'person_family_id as personFamilyId',
                    'crud_type as crudType',
                    'lov_famr as lovFamr',
                    'lov_gndr as lovGndr',
                    'lov_edul as lovEdul',
                    'birth_date as birthDate',
                    'occupation',
                    'is_emergency as isEmergency',
                    'phone',
                    'description',
                    'address'
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
        return DB::table('request_person_families')->insertGetId($obj);
    }

    /**
     * Update data Person Families Request to DB
     * @param  array obj, personFamilyId
     */
    public function update($personFamilyId, $obj)
    {
        DB::table('request_person_families')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
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
