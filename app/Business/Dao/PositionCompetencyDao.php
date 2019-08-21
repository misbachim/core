<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PositionCompetencyDao
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

        return DB::table('position_competencies')->insertGetId($obj);
    }

    /**
     * @description check positioncode and competecy model code
     */
    public function isCodeDuplicate($positionCode, $competencyModelCode)
    {
        return (DB::table('position_competencies')->where([
            ['position_code', $positionCode],
            ['competency_model_code', $competencyModelCode],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count() > 0);
    }


    public function getCompetencyCode($id)
    {
        return
            DB::table('position_competencies')
            ->select(
                'competency_models.name'
            )
            ->join('competency_models', function($join) {
            $join->on('competency_models.code','position_competencies.competency_model_code')
                ->where([
                    ['competency_models.tenant_id', $this->requester->getTenantId()],
                    ['competency_models.company_id', $this->requester->getCompanyId()]
                ]);
            })
            ->where([
                ['position_competencies.tenant_id', $this->requester->getTenantId()],
                ['position_competencies.id', $id],
                ['position_competencies.company_id', $this->requester->getCompanyId()]
            ])
            ->first();
    }


    public function getActive($positionCode)
    {
        $now = Carbon::today();
        return
            DB::table('position_competencies')
            ->select(
                'id'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['position_code', $positionCode],
                ['eff_begin', '<=', $now],
                ['eff_end', '>=', $now]
            ])
            ->first();
    }


     public function update($obj, $id)
    {
       $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('position_competencies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->update($obj);
    }


}
