<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonFamilyDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person family for one person
     * @param  tenantId, personId
     */
    public function getAll($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('person_families')
                ->selectRaw(
                    'person_families.id,'.
                    'person_families.name,'.
                    'person_families.birth_date as "birthDate",'.
                    'person_families.is_emergency as "isEmergency",'.
                    'relationships.val_data as "relationship",'.
                    'age(person_families.birth_date) as "age",'.
                    'educations.val_data as "education",'.
                    'person_families.occupation,'.
                    'person_families.is_emergency as "isEmergency",'.
                    'person_families.lov_famr as "lovFamr"'
                )
                ->leftJoin('lovs as relationships',  function ($join) use($companyId, $tenantId)  {
                    $join->on('relationships.key_data', '=', 'person_families.lov_famr')
                        ->where([
                            ['relationships.lov_type_code', 'FAMR'],
                            ['relationships.tenant_id', $tenantId],
                            ['relationships.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as educations', function ($join) use($companyId, $tenantId)  {
                    $join->on('educations.key_data', '=', 'person_families.lov_edul')
                        ->where([
                            ['educations.lov_type_code', 'EDUL'],
                            ['educations.tenant_id', $tenantId],
                            ['educations.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_families.tenant_id', $tenantId],
                    ['person_families.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person family based on personFamilyId
     * @param $personId
     * @param $personFamilyId
     * @return
     */
    public function getOne($personId, $personFamilyId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('person_families')
                ->select(
                    'id',
                    'name',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'lov_famr as lovFamr',
                    'lov_gndr as lovGndr',
                    'lov_edul as lovEdul',
                    'relationships.val_data as relationship',
                    'birth_date as birthDate',
                    'occupation',
                    'is_emergency as isEmergency',
                    'phone as phone',
                    'description'
                )
                ->leftJoin('lovs as relationships',  function ($join) use($companyId, $tenantId)  {
                    $join->on('relationships.key_data', '=', 'person_families.lov_famr')
                        ->where([
                            ['relationships.lov_type_code', 'FAMR'],
                            ['relationships.tenant_id', $tenantId],
                            ['relationships.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_families.tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['id', $personFamilyId]
                ])
                ->first();
    }

    public function getAllEmergency($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_families')
                ->select(
                    'person_families.id',
                    'person_families.name',
                    'person_families.phone',
                    'person_families.is_emergency as isEmergency',
                    'relationships.val_data as relationship'
                )
                ->distinct()
                ->join('persons', 'person_families.person_id', '=', 'persons.id')
                ->join('lovs as relationships', function ($join) use($companyId, $tenantId)  {
                    $join->on('relationships.key_data', '=', 'person_families.lov_famr')
                        ->where([
                            ['relationships.lov_type_code', 'FAMR'],
                            ['relationships.tenant_id', $tenantId],
                            ['relationships.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_families.tenant_id', '=', $tenantId],
                    ['person_families.person_id', '=', $personId],
                    ['person_families.is_emergency','=', true]
                ])
                ->get();
    }

    public function getLov($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('person_families')
                ->select(
                    'person_families.id',
                    'person_families.name',
                    'person_families.phone',
                    'person_families.is_emergency as isEmergency',
                    'relationships.val_data as relationship'
                )
                ->distinct()
                ->join('persons',  function ($join) use($companyId, $tenantId)  {
                    $join->on('person_families.person_id', '=', 'persons.id')
                        ->where([
                            ['persons.tenant_id', $tenantId]
                        ])
                        ->orderBy('persons.persons', 'desc');
                })
                ->join('lovs as relationships', function ($join) use($companyId, $tenantId)  {
                    $join->on('relationships.key_data', '=', 'person_families.lov_famr')
                        ->where([
                            ['relationships.lov_type_code', 'FAMR'],
                            ['relationships.tenant_id', $tenantId],
                            ['relationships.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_families.tenant_id', '=', $tenantId],
                    ['person_families.person_id', '=', $personId]
                ])
                ->orderBy('person_families.is_emergency', 'desc')
                ->orderBy('person_families.name', 'asc')
                ->get();
    }

    public function getLovCustomBenefit($personId)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $data = DB::table('person_families')
                ->select(
                    DB::raw('id,lov_famr as lovFamr, name, birth_date as birthDate, relationships.val_data as relationship, age(birth_date) as age')
                )
                ->join('lovs as relationships', function ($join) use($companyId, $tenantId) {
                    $join->on('relationships.key_data', '=', 'person_families.lov_famr')
                    ->where([
                        ['relationships.lov_type_code', 'FAMR'],
                        ['relationships.tenant_id', $tenantId],
                        ['relationships.company_id', $companyId]
                    ]);
                })
                ->where([
                    ['person_families.tenant_id', $tenantId],
                    ['person_families.eff_begin','<=',Carbon::now()],
                    ['person_families.eff_end','>=',Carbon::now()],
                    ['person_families.person_id', $personId]
                ])
                ->get();

//        foreach ($rulesFamily as $rule) {
//            info('$rule->lovFamr', [$rule->lovFamr]);
//            $data->where('lov_famr', $rule->lovFamr);
//        }

        return $data;
    }

    public function search($query,$personId)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        return
            DB::table('person_families')
                ->selectRaw(
                    'person_families.id,'.
                    'person_families.name'
                )
                ->join('persons',  function ($join) use($companyId, $tenantId)  {
                    $join->on('person_families.person_id', '=', 'persons.id')
                        ->where([
                            ['persons.tenant_id', $tenantId]
                        ])
                        ->orderBy('persons.persons', 'desc');
                })
                ->where([
                    ['person_families.tenant_id', $this->requester->getTenantId()],
                    ['person_families.person_id', $personId],
                    ['person_families.eff_begin', '<=', $now],
                    ['person_families.eff_end', '>=', $now]
                ])
                ->whereRaw('LOWER(person_families.name) like ?', [$searchString])
                ->get();
    }

    /**
     * Insert data person family to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_families')->insertGetId($obj);
    }

    /**
     * Update data person family to DB
     * @param $personId
     * @param $personFamilyId
     * @param $obj
     */
    public function update($personId, $personFamilyId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_families')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personFamilyId]
        ])
        ->update($obj);
    }

    /**
     * Update emergency contact person family to DB
     * @param $personId
     * @param $personFamilyId
     */
    public function setEmergencyContactFalse($personId)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();
        $obj['is_emergency'] = false;

        DB::table('person_families')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId]
            ])
            ->update($obj);
    }

    /**
     * Update emergency contact person family to DB
     * @param $personId
     * @param $personFamilyId
     */
    public function setEmergencyContactTrue($personId,$personFamilyId)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();
        $obj['is_emergency'] = true;

        DB::table('person_families')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId],
                ['id', $personFamilyId]
            ])
            ->update($obj);
    }

    /**
     * Delete data person family from DB.
     * @param $personId
     * @param $personFamilyId
     */
    public function delete($personId, $personFamilyId)
    {
        DB::table('person_families')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personFamilyId]
        ])
        ->delete();
    }
}
