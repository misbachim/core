<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompetencyGroupDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get All Competency Group
     * @param  offset, limit
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('competency_groups')
                ->select(
                    'competency_groups.id',
                    'competency_groups.code',
                    'competency_groups.name',
                    'competency_groups.description',
                    'competency_groups.eff_begin as effBegin',
                    'competency_groups.eff_end as effEnd'
                )
                ->where([
                    ['competency_groups.tenant_id', $this->requester->getTenantId()],
                    ['competency_groups.company_id', $this->requester->getCompanyId()],
                ])
                ->whereIn('competency_groups.id', function ($query) {
                    $query->select(DB::raw('MAX(competency_groups.id) as id'))
                            ->from('competency_groups')
                            ->groupBy('competency_groups.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Active Competency Group
     * @param  offset, limit
     */
    public function getAllActive($offset,$limit)
    {
        return
            DB::table('competency_groups')
                ->select(
                    'competency_groups.id',
                    'competency_groups.code',
                    'competency_groups.name',
                    'competency_groups.description',
                    'competency_groups.eff_begin as effBegin',
                    'competency_groups.eff_end as effEnd'
                )
                ->where([
                    ['competency_groups.tenant_id', $this->requester->getTenantId()],
                    ['competency_groups.company_id', $this->requester->getCompanyId()],
                    ['competency_groups.eff_begin','<=', Carbon::now()],
                    ['competency_groups.eff_end','>=', Carbon::now()],
                ])
                ->whereIn('competency_groups.id', function ($query) {
                    $query->select(DB::raw('MAX(competency_groups.id) as id'))
                            ->from('competency_groups')
                            ->groupBy('competency_groups.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Inactive Competency Group
     * @param  offset, limit
     */
    public function getAllInactive($offset,$limit)
    {
        return
            DB::table('competency_groups')
                ->select(
                    'competency_groups.id',
                    'competency_groups.code',
                    'competency_groups.name',
                    'competency_groups.description',
                    'competency_groups.eff_begin as effBegin',
                    'competency_groups.eff_end as effEnd'
                )
                ->where([
                    ['competency_groups.tenant_id', $this->requester->getTenantId()],
                    ['competency_groups.company_id', $this->requester->getCompanyId()],
                    ['competency_groups.eff_end','<', Carbon::now()],
                ])
                ->whereIn('competency_groups.id', function ($query) {
                    $query->select(DB::raw('MAX(competency_groups.id) as id'))
                            ->from('competency_groups')
                            ->groupBy('competency_groups.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get one Education Institution based on Education Institution Id
     * @param  $educationInstitutionId
     */
    public function getOne($competencyGroupId)
    {
        return
            DB::table('competency_groups')
                ->select(
                    'competency_groups.id',
                    'competency_groups.code',
                    'competency_groups.name',
                    'competency_groups.description',
                    'competency_groups.eff_begin as effBegin',
                    'competency_groups.eff_end as effEnd'
                )
                ->where([
                    ['competency_groups.tenant_id', $this->requester->getTenantId()],
                    ['competency_groups.company_id', $this->requester->getCompanyId()],
                    ['competency_groups.id', $competencyGroupId],
                ])
                ->first();
    }

    public function getCompetencyGroupCompetencies($competencyGroupId) {
        return
            DB::table('competency_group_competencies')
                ->select(
                    'id',
                    'competency_code as code',
                    'competency_group_id as competencyGroupId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['competency_group_id', $competencyGroupId]
                ])
                ->get();
    }

    public function getCompetencyByCompetencyGroupId($competencyGroupId) {
        $getData = $this->getCompetencyGroupCompetencies($competencyGroupId);
        $tempCompetencyName = [];
        if(count($getData) > 0) {
            for($i = 0 ; $i < count($getData) ; $i++)
            {
                $getDataCompetency = $this->getLastCompetency($getData[$i]->code);
                array_push($tempCompetencyName, $getDataCompetency);
            }
        }

        return $tempCompetencyName;
    }

    public function getCompetency($competencyCode) {
        return
            DB::table('competencies')
                ->select(
                    'competencies.id',
                    'competencies.code',
                    'competencies.name',
                    'competencies.description',
                    'competencies.type',
                    'competencies.rating_scale_code as ratingScaleCode',
                    'rating_scales.name as ratingScaleName',
                    'competencies.core_competency as coreCompetency',
                    'competencies.eff_begin as effBegin',
                    'competencies.eff_end as effEnd',
                    'lovs.val_data as typeName'
                )
                ->join('lovs', function ($join) {
                    $join
                        ->on('lovs.key_data', '=', 'competencies.type');
                })
                ->join('rating_scales', function ($join) {
                    $join
                        ->on('rating_scales.code', '=', 'competencies.rating_scale_code');
                })
                ->where([
                    ['lovs.lov_type_code','COMTYPE'],
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['lovs.tenant_id', $this->requester->getTenantId()],
                    ['lovs.company_id', $this->requester->getCompanyId()],
                    ['competencies.code', $competencyCode]
                ])
                ->orderBy('competencies.eff_end', 'DESC')
                ->first();
    }

    public function getLastCompetency($competencyCode) {

        return
            DB::table('competencies')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $competencyCode]
                ])
                ->orderByRaw('eff_end DESC')
                ->first();
    }

    /**
     * Get History Competency Group based on Competency Group Code
     * @param  $competencyGroupCode, $competencyGroupId
     */
    public function getHistory($competencyGroupCode, $competencyGroupId) {

        return
            DB::table('competency_groups')
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
                    ['code', $competencyGroupCode],
                    ['id', '!=', $competencyGroupId]
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    /**
     * Insert data Competency Group to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'competency_groups', $obj);

        return DB::table('competency_groups')->insertGetId($obj);
    }

    /**
     * Insert data Competency Group Competencies to DB
     * @param  array $obj
     */
    public function saveCompetencyGroupCompetencies($obj) {

        LogDao::insertLogImpact($this->requester->getLogId(), 'competency_group_competencies', $obj);

        DB::table('competency_group_competencies')->insert($obj);
    }

    /**
     * Update data Competency Group to DB
     * @param  array obj, competencyGroupId
     */
    public function update($competencyGroupId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'competency_groups', $obj);

        DB::table('competency_groups')
        ->where([
            ['id', $competencyGroupId]
        ])
        ->update($obj);
    }

    public function structureGetCompetencyList($competencyGroupId) {

        $dataCompetencyGroupDetail = $this->getCompetencyGroupCompetencies($competencyGroupId);

        $countCm = count($dataCompetencyGroupDetail);
        if($countCm > 0) {
            for($i = 0 ; $i < $countCm ; $i++) {
                $tempCompetency = $this->getCompetency($dataCompetencyGroupDetail[$i]->code);
                $dataCompetencyGroupDetail[$i]->name = $tempCompetency->name;
                $dataCompetencyGroupDetail[$i]->description = $tempCompetency->description;
                $dataCompetencyGroupDetail[$i]->type = $tempCompetency->typeName;
                $dataCompetencyGroupDetail[$i]->ratingScaleName = $tempCompetency->ratingScaleName;
                $dataCompetencyGroupDetail[$i]->coreCompetency = $tempCompetency->coreCompetency;
                $behaviours = $this->getBehaviour($tempCompetency->id);
                if(count($behaviours) > 0) {
                    $dataCompetencyGroupDetail[$i]->behaviour = 'Yes';
                } else {
                    $dataCompetencyGroupDetail[$i]->behaviour = 'No';
                }
            }
        } else {
            $dataCompetencyGroupDetail = [];
        }
        return $dataCompetencyGroupDetail;
    }

    /**
     * Get Competency Behaviour
     */
    public function getBehaviour($itemId)
    {
        return
            DB::table('item_behaviours')
                ->select(
                    'behaviour',
                    'item_id as itemId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['item_id', $itemId]
                ])
                ->get();
    }

    /**
     * @param string $code
     * @return
     */
    public function isCodeDuplicate(string $code)
    {
        return DB::table('competency_groups')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    /**
     * Get LOV all Competency Groups in ONE company
     */
    public function getLov() {

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
                    ['eff_begin','<=', Carbon::now()],
                    ['eff_end','>=', Carbon::now()]
                ])
                ->whereIn('competency_groups.id', function ($query) {
                    $query->select(DB::raw('MAX(competency_groups.id) as id'))
                            ->from('competency_groups')
                            ->groupBy('competency_groups.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    public function getLastCompetencyModel($competencyModelId) {

        return
            DB::table('competency_models')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $competencyModelId],
                    ['eff_begin','<=', Carbon::now()],
                    ['eff_end','>=', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->first();
    }

    public function getCompetencyModelCompetencyGroups($competencyGroupCode) {

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
                    ['competency_group_code', $competencyGroupCode],
                    ])
                ->get();
    }

    public function getCompetencyModelByCompetencyGroupCode($competencyGroupCode) {

        $getData = $this->getCompetencyModelCompetencyGroups($competencyGroupCode);
        $tempCompetencyModelName = [];
        if(count($getData) > 0) {
            for($i = 0 ; $i < count($getData) ; $i++)
            {
                $getDataCompetencyModel = $this->getLastCompetencyModel($getData[$i]->competencyModelId);
                if(!empty($getDataCompetencyModel)){
                    array_push($tempCompetencyModelName, $getDataCompetencyModel);
                }
            }
        }

        return $tempCompetencyModelName;
    }
}
