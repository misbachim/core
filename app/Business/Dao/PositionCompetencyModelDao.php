<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PositionCompetencyModelDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all position_competency_models from DB for a position.
     * @param positionId
     */
    public function getOne($positionCode)
    {
        info('tenantId', array($this->requester->getTenantId()));
        info('companyId', array($this->requester->getCompanyId()));
        return
            DB::table('position_competency_models')
                ->select(
                    'id',
                    'competency_model_code as competencyModelCode',
                    'position_code as positionCode',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['position_code', $positionCode],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }
    /**
     * Get all position_competency_model_details from DB for a position.
     * @param positionId
     */
    public function getAllDetails($competencyModelId)
    {
        return
            DB::table('position_competency_model_details')
                ->select(
                    'id',
                    'position_competency_model_id as positionCompetencyModelId',
                    'competency_id as competencyId',
                    'rating_scale_detail_id as ratingScaleDetailId',
                    'used_in_process_review as usedInProcessReview'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['position_competency_model_id', $competencyModelId],
                ])
                ->get();
    }

    public function getAllDetailsByRatingScaleDetailId($ratingScaleDetailId) {
        return
            DB::table('position_competency_model_details')
                ->select(
                    'id',
                    'position_competency_model_id as positionCompetencyModelId',
                    'competency_id as competencyId',
                    'rating_scale_detail_id as ratingScaleDetailId',
                    'used_in_process_review as usedInProcessReview'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['rating_scale_detail_id', $ratingScaleDetailId],
                ])
                ->get();
    }

    public function getHistory($positionCompetencyModelId, $positionCode) {
        return
            DB::table('position_competency_models')
                ->select(
                    'id',
                    'competency_model_code as competencyModelCode',
                    'position_code as positionCode',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['position_code', $positionCode],
                    ['id', '!=', $positionCompetencyModelId]
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    /**
     * Insert position grades data into DB.
     * @param obj
     */
    public function save($obj)
    {
        return DB::table('position_competency_models')->insertGetId($obj);
    }

    /**
     * Insert position grades data into DB.
     * @param obj
     */
    public function update($positionCompetencyModelId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('position_competency_models')
        ->where([
            ['id', $positionCompetencyModelId]
        ])
        ->update($obj);
    }

    /**
     * Insert position grades data into DB.
     * @param obj
     */
    public function saveDetail($obj)
    {
        DB::table('position_competency_model_details')->insert($obj);
    }

    // /**
    //  * Delete position grades data from DB by id.
    //  * @param positionId
    //  */
    // public function delete($positionCode)
    // {
    //     DB::table('position_competency_models')->where('position_code', $positionCode)->delete();
    // }
}
