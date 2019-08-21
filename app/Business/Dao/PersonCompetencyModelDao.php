<?php
namespace App\Business\Dao;


use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonCompetencyModelDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function getAll($employeeId) {

        return
            DB::table('employee_competency_models')
                ->select(
                    'id',
                    'competency_model_code as competencyModelCode',
                    'employee_id as employeeId',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['employee_id',  $employeeId],
                    ['eff_begin','<=', Carbon::now()],
                    ['eff_end','>=', Carbon::now()]
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }

    public function getDetail($employeeCompetencyModelId) {

        return
            DB::table('employee_competency_model_details')
                ->select(
                    'id',
                    'employee_competency_model_id as employeeCompetencyModelId',
                    'competency_id as competencyId',
                    'rating_scale_detail_id as ratingScaleDetailId',
                    'used_in_process_review as usedInProcessReview'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['employee_competency_model_id',  $employeeCompetencyModelId]
                ])
                ->get();
    }

    public function getAllDetailsByRatingScaleDetailId($ratingScaleDetailId) {

        return
            DB::table('employee_competency_model_details')
                ->select(
                    'id',
                    'employee_competency_model_id as employeeCompetencyModelId',
                    'competency_id as competencyId',
                    'rating_scale_detail_id as ratingScaleDetailId',
                    'used_in_process_review as usedInProcessReview'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['rating_scale_detail_id', $ratingScaleDetailId]
                ])
                ->get();
    }

    public function getAllEmployeeIdByCompetencyModelCode($competencyModelCode){
        return
            DB::table('employee_competency_models')
                ->select(
                    'employee_id as employeeId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['competency_model_code', $competencyModelCode]
                ])
                ->groupBy('employee_id')
                ->get();
    }

    public function getHistory($personCompetencyModelId, $employeeId) {
        return
            DB::table('employee_competency_models')
                ->select(
                    'id',
                    'competency_model_code as competencyModelCode',
                    'employee_id as employeeId',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['employee_id', $employeeId],
                    ['id', '!=', $personCompetencyModelId]
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    public function update($personCompetencyModelId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('employee_competency_models')
            ->where([
                ['id', $personCompetencyModelId]
            ])
            ->update($obj);
    }

    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('employee_competency_models')->insertGetId($obj);
    }

    public function savePersonCompetencyModelDetails($obj) {

        DB::table('employee_competency_model_details')->insert($obj);
    }


}
