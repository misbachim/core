<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Dao\CompetencyDao;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class CompetencyModelDao
{
    public function __construct(Requester $requester, CompetencyDao $competencyDao)
    {
        $this->requester = $requester;
        $this->competencyDao = $competencyDao;
    }

    /**
     * Get all active Competency Model
     * @param  offset, limit
     */
    public function getAllActive($offset,$limit)
    {
        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin','<=', Carbon::now()],
                    ['eff_end','>=', Carbon::now()]
                ])
                ->whereIn('competency_models.id', function ($query) {
                    $query->select(DB::raw('MAX(competency_models.id) as id'))
                            ->from('competency_models')
                            ->groupBy('competency_models.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all active Competency Model
     * @param  offset, limit
     */
    public function getAllInactive($offset,$limit)
    {
        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_end','<', Carbon::now()]
                ])
                ->whereIn('competency_models.id', function ($query) {
                    $query->select(DB::raw('MAX(competency_models.id) as id'))
                            ->from('competency_models')
                            ->groupBy('competency_models.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Competency Model
     * @param  offset, limit
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->whereIn('competency_models.id', function ($query) {
                    $query->select(DB::raw('MAX(competency_models.id) as id'))
                            ->from('competency_models')
                            ->groupBy('competency_models.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }


    /**
     * @description get all competency model
     */
    public function getAllWithoutLimit($code)
    {
        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $code]
                ])
                ->first();
    }


    /**
     * @description get all data competency group by competency id
     */
    public function getAllGroupByModelId($modelId)
    {
        return
            DB::table('competency_model_competency_groups')
                ->select(
                    'competency_groups.id as competencyGroupId'
                )
                ->join('competency_groups', function($join) {
                    $join->on('competency_groups.code', 'competency_model_competency_groups.competency_group_code');
                })
                ->where([
                    ['competency_model_competency_groups.tenant_id', $this->requester->getTenantId()],
                    ['competency_model_competency_groups.company_id', $this->requester->getCompanyId()],
                    ['competency_model_competency_groups.competency_model_id', $modelId],
                    ['competency_groups.tenant_id', $this->requester->getTenantId()],
                    ['competency_groups.company_id', $this->requester->getCompanyId()]
                ])
                ->orderByRaw('competency_model_competency_groups.id ASC')
                ->get();
    }


    /**
     * @description get all data competecncy by group id
     */
    public function getAllCompetencyByGroupId($groupId)
    {
        return
            DB::table('competency_group_competencies')
                ->select(
                    'competency_code'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['competency_group_id', $groupId]
                ])
                ->orderByRaw('id ASC')
                ->get();
    }

    /**
     * Get one CompetencyModel based on Competency Model Id
     * @param  $competencyModelId
     * @param  $companyId
     */
    public function getOne($competencyModelId, $companyId)
    {
        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['id', $competencyModelId],
                ])
                ->first();
    }

    /**
     * Get last one CompetencyModel based on Competency Model Code
     * @param  $competencyModelCode
     * @param  $companyId
     */
    public function getLastOne($competencyModelCode, $companyId)
    {
        info('$competencyModelCode', [$competencyModelCode]);
        info('$companyId', [$companyId]);
        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['code', $competencyModelCode],
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }

    public function getCompetencyModelCompetencyGroups($competencyModelId) {

        return
            DB::table('competency_model_competency_groups')
                ->select(
                    'id',
                    'competency_group_code as competencyGroupCode',
                    'competency_model_id as competencyModelId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['competency_model_id', $competencyModelId]
                ])
                ->get();
    }

    public function getLastCompetencyGroup($competencyGroupCode) {

        return
            DB::table('competency_groups')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $competencyGroupCode]
                ])
                ->orderByRaw('eff_end DESC')
                ->first();
    }

    public function getCompetencyGroupCompetency($competencyGroupId){

        return
            DB::table('competency_group_competencies')
                ->select(
                    'id',
                    'competency_code as competencyCode',
                    'competency_group_id as competencyGroupId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['competency_group_id', $competencyGroupId]
                ])
                ->get();
    }

    public function getCompetencyGroupByCompetencyModelId($competencyModelId) {

        $getData = $this->getCompetencyModelCompetencyGroups($competencyModelId);
        $tempCompetencyGroupName = [];
        if(count($getData) > 0) {
            for($i = 0 ; $i < count($getData) ; $i++)
            {
                $getDataCompetencyGroup = $this->getLastCompetencyGroup($getData[$i]->competencyGroupCode);
                array_push($tempCompetencyGroupName, $getDataCompetencyGroup);
            }
        }

        return $tempCompetencyGroupName;
    }

    /**
     * Get History Competency Model based on Competency Model Code
     * @param  $competencyModelCode, $competencyModelId
     */
    public function getHistory($competencyModelCode, $competencyModelId) {

        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $competencyModelCode],
                    ['id', '!=', $competencyModelId]
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    /**
     * Get LOV all Competencies Model in ONE company
     */
    public function getLov()
    {
        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    public function getAllPositionCodeByCompetencyModelCode($competencyModelCode) {
        return
            DB::table('position_competency_models')
                ->select(
                    'position_code as positionCode'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['competency_model_code', $competencyModelCode]
                ])
                ->groupBy('position_code')
                ->get();
    }

    /**
     * Insert data Competency Model to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'competency_models', $obj);

        return DB::table('competency_models')->insertGetId($obj);
    }

    /**
     * Insert data Competency Model Competency Groups to DB
     * @param  array $obj
     */
    public function saveCompetencyModelCompetencyGroups($obj) {

        LogDao::insertLogImpact($this->requester->getLogId(), 'competency_model_competency_groups', $obj);

        DB::table('competency_model_competency_groups')->insert($obj);
    }

    /**
     * Update data Competency Model to DB
     * @param  array $obj, $competencyModelId
     */
    public function update($competencyModelId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'competency_models', $obj);

        DB::table('competency_models')
            ->where([
                ['id', $competencyModelId]
            ])
            ->update($obj);
    }

    public function structureGetCompetencyGroupList($competencyModelId) {

        $dataCompetencyModelDetail = $this->getCompetencyModelCompetencyGroups($competencyModelId);

        $countCm = count($dataCompetencyModelDetail);
        if($countCm > 0) {
            for($i = 0 ; $i < $countCm ; $i++) {
                $tempCompetencyGroup = $this->getLastCompetencyGroup($dataCompetencyModelDetail[$i]->competencyGroupCode);
                $dataCompetencyModelDetail[$i]->code = $tempCompetencyGroup->code;
                $dataCompetencyModelDetail[$i]->name = $tempCompetencyGroup->name;
                $dataCompetencyModelDetail[$i]->description = $tempCompetencyGroup->description;

                $tempCompetencyGroupCompetency = $this->getCompetencyGroupCompetency($tempCompetencyGroup->id);
                $countCgc = count($tempCompetencyGroupCompetency);
                if($countCgc > 0) {
                    $tempCompetency= array();
                    for($y = 0; $y < $countCgc ; $y++ ) {
                        $dataCompetency = $this->competencyDao->getOne($tempCompetencyGroupCompetency[$y]->competencyCode);
                        array_push($tempCompetency,
                            ['name' => $dataCompetency->name]
                        );
                    }
                    $dataCompetencyModelDetail[$i]->competencyList = $tempCompetency;
                }else{
                    $dataCompetencyModelDetail[$i]->competencyList = [];
                }
            }
        } else {
            $dataCompetencyModelDetail = [];
        }
        return $dataCompetencyModelDetail;
    }

    /**
     * @param string $code
     * @return
     */
    public function isCodeDuplicate(string $code)
    {
        return DB::table('competency_models')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

}
