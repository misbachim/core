<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonEducationDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person education for one person
     * @param personId
     */
    public function getAll($personId)
    {
        $now = Carbon::now();
        return
            DB::table('person_educations')
                ->select(
                    'id',
                    'lov_edul as lovEdul',
                    'institution',
                    'subject as specializationCode',
                    'grade',
                    'max_grade as maxGrade',
                    'year_begin as yearBegin',
                    'year_end as yearEnd'
                )
                ->where([
                    ['person_educations.tenant_id', $this->requester->getTenantId()],
                    ['person_educations.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person education based on personEducationId
     * @param personId, personEducationId
     */
    public function getOne($personId, $personEducationId)
    {
        return
            DB::table('person_educations')
                ->select(
                    'id',
                    'lov_edul as lovEdul',
                    'institution',
                    'subject',
                    'grade',
                    'max_grade as maxGrade',
                    'year_begin as yearBegin',
                    'year_end as yearEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['id', $personEducationId]
                ])
                ->first();
    }

    public function getAllPersonIdByInstitution($institutionName){
        return
            DB::table('person_educations')
                ->select(
                    'person_id as personId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['institution', $institutionName]
                ])
                ->groupBy('person_id')
                ->get();
    }

    public function getAllPersonIdBySpecialization($specializationCode){
        return
            DB::table('person_educations')
                ->select(
                    'person_id as personId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['subject', $specializationCode]
                ])
                ->groupBy('person_id')
                ->get();
    }

    public function getOneEducationInstitution($institutionName) {
        return
            DB::table('education_institutions')
                ->select(
                    'education_institutions.id',
                    'education_institutions.name',
                    'education_institutions.address'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['name', $institutionName]
                ])
                ->first();
    }

    public function getOneEducationSpecialization($specializationCode) {
        return
            DB::table('education_specializations')
                ->select(
                    'education_specializations.id',
                    'education_specializations.code',
                    'education_specializations.name'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $specializationCode]
                ])
                ->first();
    }

    /**
     * Insert data person education to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_educations')->insertGetId($obj);
    }

    /**
     * Update data person education to DB
     * @param  array obj, personId, personEducationId
     */
    public function update($personId, $personEducationId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_educations')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personEducationId]
        ])
        ->update($obj);
    }

    /**
     * Delete data person education from DB.
     * @param personId, personEducationId
     */
    public function delete($personId, $personEducationId)
    {
        DB::table('person_educations')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personEducationId]
        ])
        ->delete();
    }
}
