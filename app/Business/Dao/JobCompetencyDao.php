<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobCompetencyDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * @description save data into database
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('job_competencies')->insertGetId($obj);
    }

    /**
     * @description check jobcode and competecy model code
     */
    public function isCodeDuplicate($jobCode, $competencyModelCode)
    {
        return (DB::table('job_competencies')->where([
            ['job_code', $jobCode],
            ['competency_model_code', $competencyModelCode],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count() > 0);
    }


    public function getCompetencyCode($id)
    {
        return
            DB::table('job_competencies')
            ->select(
                'competency_models.name'
            )
            ->join('competency_models', function($join) {
            $join->on('competency_models.code','job_competencies.competency_model_code')
                ->where([
                    ['competency_models.tenant_id', $this->requester->getTenantId()],
                    ['competency_models.company_id', $this->requester->getCompanyId()]
                ]);
            })
            ->where([
                ['job_competencies.tenant_id', $this->requester->getTenantId()],
                ['job_competencies.id', $id],
                ['job_competencies.company_id', $this->requester->getCompanyId()]
            ])
            ->first();
    }


    public function getActive($jobCode)
    {
        $now = Carbon::today();
        return
            DB::table('job_competencies')
            ->select(
                'id'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['job_code', $jobCode],
                ['eff_begin', '<=', $now],
                ['eff_end', '>=', $now]
            ])
            ->first();
    }


     public function update($obj, $id)
    {
       $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('job_competencies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->update($obj);
    }


}
