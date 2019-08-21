<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobCompetencyListDao
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
        DB::table('job_competency_lists')->insert($obj);
    }


    public function getAllByJobCode($jobCode, $offset, $limit)
    {
        $now = Carbon::today();
        return
            DB::table('job_competency_lists')
            ->select(
                'job_competency_lists.id',
                'job_competency_lists.essential',
                'job_competency_lists.rating_scale_detail_id as ratingScaleDetailId',
                'job_competency_lists.margin_value as marginValue',
                'job_competency_lists.margin_level as marginLevel',
                'job_competency_lists.use_in_review as useInReview',
                'job_competency_lists.job_competency_id as jobCompetencyId',
                'job_competency_lists.eff_begin as effBegin',
                'job_competency_lists.eff_end as effEnd',
                'competencies.type',
                'competencies.name'

            )
            ->join('competencies', function($join) {
            $join->on('competencies.code','job_competency_lists.competency_code')
                ->where([
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()]
                ]);
            })
            ->where([
                ['job_competency_lists.tenant_id', $this->requester->getTenantId()],
                ['job_competency_lists.job_code', $jobCode],
                ['job_competency_lists.company_id', $this->requester->getCompanyId()],
                ['job_competency_lists.eff_begin', '<=', $now],
                ['job_competency_lists.eff_end', '>=', $now]
            ])
            ->offset($offset)
            ->limit($limit)
            ->get();
    }


    public function getTotalRows($code)
    {
        return
            DB::table('job_competency_lists')
            ->where([
                ['job_code', $code],
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }

    public function getAllByCompetencyId($id)
    {
        return
            DB::table('job_competency_lists')
            ->select(
                'id'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['job_competency_id', $id]
            ])
            ->get();
    }


    public function getAllIdByCompetencyJobCode($jobCode)
    {
        return
            DB::table('job_competency_lists')
            ->select(
                'id'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['job_code', $jobCode]
            ])
            ->get();
    }


    public function update($obj, $id)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('job_competency_lists')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->update($obj);
    }
}
