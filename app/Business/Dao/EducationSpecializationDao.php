<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EducationSpecializationDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Education Specialization
     */
    public function getAll()
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        return
            DB::table('education_specializations')
                ->select(
                    'education_specializations.id',
                    'education_specializations.code',
                    'education_specializations.name',
                    'education_specializations.description',
                    'lovs.val_data as categoryName',
                    'education_specializations.lov_category_education as lovCategoryEducation',
                    'education_specializations.eff_begin as effBegin',
                    'education_specializations.eff_end as effEnd'
                )
                ->leftJoin('lovs', function ($join) use ($companyId, $tenantId) {
                    $join->on('lovs.key_data', '=', 'education_specializations.lov_category_education')
                        ->where([
                            ['lovs.tenant_id', $tenantId],
                            ['lovs.company_id', $companyId],
                            ['lovs.lov_type_code', 'EDUS']
                        ]);
                })
                ->where([
                    ['education_specializations.tenant_id', $tenantId],
                    ['education_specializations.company_id', $companyId]
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get all active Education Specialization
     */
    public function getAllActive()
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('education_specializations')
                ->select(
                    'education_specializations.id',
                    'education_specializations.code',
                    'education_specializations.name',
                    'education_specializations.description',
                    'lovs.val_data as categoryName',
                    'education_specializations.lov_category_education as lovCategoryEducation',
                    'education_specializations.eff_begin as effBegin',
                    'education_specializations.eff_end as effEnd'
                )
                ->leftJoin('lovs', function ($join) use ($companyId, $tenantId) {
                    $join->on('lovs.key_data', '=', 'education_specializations.lov_category_education')
                        ->where([
                            ['lovs.tenant_id', $tenantId],
                            ['lovs.company_id', $companyId],
                            ['lovs.lov_type_code', 'EDUS']
                        ]);
                })
                ->where([
                    ['education_specializations.tenant_id', $tenantId],
                    ['education_specializations.company_id', $companyId],
                    ['eff_begin','<=', Carbon::now()],
                    ['eff_end','>=', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get all active Education Specialization
     */
    public function getAllInactive()
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $now = Carbon::now(new \DateTimeZone('Asia/Jakarta'));
        return
            DB::table('education_specializations')
                ->select(
                    'education_specializations.id',
                    'education_specializations.code',
                    'education_specializations.name',
                    'education_specializations.description',
                    'lovs.val_data as categoryName',
                    'education_specializations.lov_category_education as lovCategoryEducation',
                    'education_specializations.eff_begin as effBegin',
                    'education_specializations.eff_end as effEnd'
                )
                ->leftJoin('lovs', function ($join) use ($companyId, $tenantId) {
                    $join->on('lovs.key_data', '=', 'education_specializations.lov_category_education')
                        ->where([
                            ['lovs.tenant_id', $tenantId],
                            ['lovs.company_id', $companyId],
                            ['lovs.lov_type_code', 'EDUS']
                        ]);
                })
                ->where([
                    ['education_specializations.tenant_id', $tenantId],
                    ['education_specializations.company_id', $companyId],
                    ['eff_end','<', $now->format('Y-m-d')]
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get one Education Specialization based on Education Specialization Id
     * @param  $educationSpecializationId
     */
    public function getOne($educationSpecializationId)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('education_specializations')
                ->select(
                    'education_specializations.id',
                    'education_specializations.code',
                    'education_specializations.name',
                    'education_specializations.description',
                    'lovs.val_data as categoryName',
                    'education_specializations.lov_category_education as lovCategoryEducation',
                    'education_specializations.eff_begin as effBegin',
                    'education_specializations.eff_end as effEnd'
                )
                ->leftJoin('lovs', function ($join) use ($companyId, $tenantId) {
                    $join->on('lovs.key_data', '=', 'education_specializations.lov_category_education')
                        ->where([
                            ['lovs.tenant_id', $tenantId],
                            ['lovs.company_id', $companyId],
                            ['lovs.lov_type_code', 'EDUS']
                        ]);
                })
                ->where([
                    ['education_specializations.tenant_id', $tenantId],
                    ['education_specializations.company_id', $companyId],
                    ['education_specializations.id', $educationSpecializationId],
                ])
                ->first();
    }

    /**
     * Get History Education Specialization based on Education Specialization Code
     * @param  educationSpecializationCode, educationSpecializationId
     */
    public function getHistory($educationSpecializationCode, $educationSpecializationId)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('education_specializations')
                ->select(
                    'education_specializations.id',
                    'education_specializations.code',
                    'education_specializations.name',
                    'education_specializations.description',
                    'lovs.val_data as categoryName',
                    'education_specializations.lov_category_education as lovCategoryEducation',
                    'education_specializations.eff_begin as effBegin',
                    'education_specializations.eff_end as effEnd'
                )
                ->leftJoin('lovs', function ($join) use ($companyId, $tenantId) {
                    $join->on('lovs.key_data', '=', 'education_specializations.lov_category_education')
                        ->where([
                            ['lovs.tenant_id', $tenantId],
                            ['lovs.company_id', $companyId],
                            ['lovs.lov_type_code', 'EDUS']
                        ]);
                })
                ->where([
                    ['education_specializations.tenant_id', $tenantId],
                    ['education_specializations.company_id', $companyId],
                    ['education_specializations.code', $educationSpecializationCode],
                    ['education_specializations.id', '!=', $educationSpecializationId]
                ])
                ->orderBy('education_specializations.eff_end', 'DESC')
                ->get();
    }

    /**
     * Insert data education_specialization to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'education_specializations', $obj);

        return DB::table('education_specializations')->insertGetId($obj);
    }

    /**
     * Update data education_specialization to DB
     * @param  array obj, educationSpecializationId
     */
    public function update($educationSpecializationId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'education_specializations', $obj);

        DB::table('education_specializations')
            ->where([
                ['id', $educationSpecializationId]
            ])
            ->update($obj);
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateEducationSpecializationCode(string $code)
    {
        return DB::table('education_specializations')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

}
