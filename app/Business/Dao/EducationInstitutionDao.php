<?php
namespace App\Business\Dao;


use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EducationInstitutionDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Education Institution
     * @param  offset, limit
     */
    public function getAll()
    {
        return
            DB::table('education_institutions')
                ->select(
                    'education_institutions.id',
                    'education_institutions.name',
                    'education_institutions.address',
                    'countries.name as countryName',
                    'education_institutions.country_code as countryCode',
                    'lov_acreditation as lovAcreditation',
                    'education_institutions.eff_begin as effBegin',
                    'education_institutions.eff_end as effEnd',
                    'education_institutions.link_website as linkWebsite'
                )
                ->leftJoin('countries', function ($join) {
                    $join->on('countries.code', '=', 'education_institutions.country_code')
                        ->where([
                            ['countries.tenant_id', $this->requester->getTenantId()],
                            ['countries.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['education_institutions.tenant_id', $this->requester->getTenantId()],
                    ['education_institutions.company_id', $this->requester->getCompanyId()],
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get all Active Education Institution
     */
    public function getAllActive()
    {
        return
            DB::table('education_institutions')
                ->select(
                    'education_institutions.id',
                    'education_institutions.name',
                    'education_institutions.address',
                    'countries.name as countryName',
                    'education_institutions.country_code as countryCode',
                    'lov_acreditation as lovAcreditation',
                    'education_institutions.eff_begin as effBegin',
                    'education_institutions.eff_end as effEnd',
                    'education_institutions.link_website as linkWebsite'
                )
                ->leftJoin('countries', function ($join) {
                    $join->on('countries.code', '=', 'education_institutions.country_code')
                        ->where([
                            ['countries.tenant_id', $this->requester->getTenantId()],
                            ['countries.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['education_institutions.tenant_id', $this->requester->getTenantId()],
                    ['education_institutions.company_id', $this->requester->getCompanyId()],
                    ['education_institutions.eff_begin','<=', Carbon::now()],
                    ['education_institutions.eff_end','>=', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get all Active Education Institution
     */
    public function getAllInactive()
    {
        $now = Carbon::now(new \DateTimeZone('Asia/Jakarta'));
        return
            DB::table('education_institutions')
                ->select(
                    'education_institutions.id',
                    'education_institutions.name',
                    'education_institutions.address',
                    'countries.name as countryName',
                    'education_institutions.country_code as countryCode',
                    'lov_acreditation as lovAcreditation',
                    'education_institutions.eff_begin as effBegin',
                    'education_institutions.eff_end as effEnd',
                    'education_institutions.link_website as linkWebsite'
                )
                ->leftJoin('countries', function ($join) {
                    $join->on('countries.code', '=', 'education_institutions.country_code')
                        ->where([
                            ['countries.tenant_id', $this->requester->getTenantId()],
                            ['countries.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['education_institutions.tenant_id', $this->requester->getTenantId()],
                    ['education_institutions.company_id', $this->requester->getCompanyId()],
                    ['education_institutions.eff_end','<', $now->format('Y-m-d')]
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get one Education Institution based on Education Institution Id
     * @param  $educationInstitutionId
     */
    public function getOne($educationInstitutionId)
    {
        return
            DB::table('education_institutions')
                ->select(
                    'education_institutions.id',
                    'education_institutions.name',
                    'education_institutions.address',
                    'countries.name as countryName',
                    'education_institutions.country_code as countryCode',
                    'lov_acreditation as lovAcreditation',
                    'education_institutions.eff_begin as effBegin',
                    'education_institutions.eff_end as effEnd',
                    'education_institutions.link_website as linkWebsite'
                )
                ->leftJoin('countries', function ($join) {
                    $join->on('countries.code', '=', 'education_institutions.country_code')
                    ->where([
                        ['countries.tenant_id', $this->requester->getTenantId()],
                        ['countries.company_id', $this->requester->getCompanyId()]
                    ]);
                })
                ->where([
                    ['education_institutions.tenant_id', $this->requester->getTenantId()],
                    ['education_institutions.company_id', $this->requester->getCompanyId()],
                    ['education_institutions.id', $educationInstitutionId],
                ])
                ->first();
    }

    /**
     * Get History Education Institution based on Education Institution Name
     * @param  educationInstitutionName, educationInstitutionId
    */
    public function getHistory($educationInstitutionName, $educationInstitutionId) {

        return
            DB::table('education_institutions')
                ->select(
                    'education_institutions.id',
                    'education_institutions.name',
                    'education_institutions.address',
                    'countries.name as countryName',
                    'education_institutions.country_code as countryCode',
                    'lov_acreditation as lovAcreditation',
                    'education_institutions.eff_begin as effBegin',
                    'education_institutions.eff_end as effEnd',
                    'education_institutions.link_website as linkWebsite'
                )
                ->leftJoin('countries', function ($join) {
                    $join->on('countries.code', '=', 'education_institutions.country_code')
                        ->where([
                            ['countries.tenant_id', $this->requester->getTenantId()],
                            ['countries.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['education_institutions.tenant_id', $this->requester->getTenantId()],
                    ['education_institutions.company_id', $this->requester->getCompanyId()],
                    ['education_institutions.name', $educationInstitutionName],
                    ['education_institutions.id', '!=', $educationInstitutionId],
                ])
                ->orderBy('education_institutions.eff_end', 'DESC')
                ->get();
    }

    /**
     * Insert data education_institution to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'education_institutions', $obj);

        return DB::table('education_institutions')->insertGetId($obj);
    }

    /**
     * Update data education_institution to DB
     * @param  array obj, educationInstitutionId
     */
    public function update($educationInstitutionId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'education_institutions', $obj);

        DB::table('education_institutions')
        ->where([
            ['id', $educationInstitutionId]
        ])
        ->update($obj);
    }

    /**
     * @param string $name
     * @return
     */
    public function checkDuplicateEducationInstitutionName(string $name)
    {
        return DB::table('education_institutions')->where([
            ['name', $name],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }


}
