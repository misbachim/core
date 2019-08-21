<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PositionCompetencyListDao
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
        DB::table('position_competency_lists')->insert($obj);
    }


    public function getAllByPositionCode($positionCode, $offset, $limit)
    {
        $now = Carbon::today();
        return
            DB::table('position_competency_lists')
            ->select(
                'position_competency_lists.id',
                'position_competency_lists.essential',
                'position_competency_lists.rating_scale_detail_id as ratingScaleDetailId',
                'position_competency_lists.margin_value as marginValue',
                'position_competency_lists.margin_level as marginLevel',
                'position_competency_lists.use_in_review as useInReview',
                'position_competency_lists.position_competency_id as positionCompetencyId',
                'position_competency_lists.eff_begin as effBegin',
                'position_competency_lists.eff_end as effEnd',
                'competencies.type',
                'competencies.name'

            )
            ->join('competencies', function($join) {
            $join->on('competencies.code','position_competency_lists.competency_code')
                ->where([
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()]
                ]);
            })
            ->where([
                ['position_competency_lists.tenant_id', $this->requester->getTenantId()],
                ['position_competency_lists.position_code', $positionCode],
                ['position_competency_lists.company_id', $this->requester->getCompanyId()],
                ['position_competency_lists.eff_begin', '<=', $now],
                ['position_competency_lists.eff_end', '>=', $now]
            ])
            ->offset($offset)
            ->limit($limit)
            ->get();
    }


    public function getTotalRows($code)
    {
        return
            DB::table('position_competency_lists')
            ->where([
                ['position_code', $code],
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }

    public function getAllByCompetencyId($id)
    {
        return
            DB::table('position_competency_lists')
            ->select(
                'id'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['position_competency_id', $id]
            ])
            ->get();
    }


    public function getAllIdByCompetencyPositionCode($positionCode)
    {
        return
            DB::table('position_competency_lists')
            ->select(
                'id'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['position_code', $positionCode]
            ])
            ->get();
    }


    public function update($obj, $id)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('position_competency_lists')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->update($obj);
    }
}
