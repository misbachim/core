<?php
namespace App\Business\Dao;

use Log;
use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompetencyDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Competencies Order By Eff End
     * @param  offset, limit
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('competencies')
                ->select(
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency as coreCompetency',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'lovs.val_data as typeName'
                )
                ->join('lovs', function ($join) {
                    $join
                        ->on('lovs.key_data', '=', 'competencies.type');
                })
                ->where([
                    ['lovs.lov_type_code','COMTYPE'],
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['lovs.tenant_id', $this->requester->getTenantId()],
                    ['lovs.company_id', $this->requester->getCompanyId()],
                ])
                ->whereIn('competencies.id', function ($query) {
                    $query->select(DB::raw('MAX(competencies.id) as id'))
                            ->from('competencies')
                            ->groupBy('competencies.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Active Competency Order By Eff End
     * @param  offset, limit
     */
    public function getAllActive($offset,$limit)
    {
        return
            DB::table('competencies')
                ->select(
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency as coreCompetency',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'lovs.val_data as typeName'
                )
                ->join('lovs', function ($join) {
                    $join
                        ->on('lovs.key_data', '=', 'competencies.type');
                })
                ->where([
                    ['lovs.lov_type_code','COMTYPE'],
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['lovs.tenant_id', $this->requester->getTenantId()],
                    ['lovs.company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->whereIn('competencies.id', function ($query) {
                    $query->select(DB::raw('MAX(competencies.id) as id'))
                            ->from('competencies')
                            ->groupBy('competencies.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Active Competency Order By Eff End
     * @param  offset, limit
     */
    public function getAllInactive($offset,$limit)
    {
        return
            DB::table('competencies')
                ->select(
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency as coreCompetency',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'lovs.val_data as typeName'
                )
                ->join('lovs', function ($join) {
                    $join
                        ->on('lovs.key_data', '=', 'competencies.type');
                })
                ->where([
                    ['lovs.lov_type_code','COMTYPE'],
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['lovs.tenant_id', $this->requester->getTenantId()],
                    ['lovs.company_id', $this->requester->getCompanyId()],
                    ['eff_end', '<', Carbon::now()]
                ])
                ->whereIn('competencies.id', function ($query) {
                    $query->select(DB::raw('MAX(competencies.id) as id'))
                            ->from('competencies')
                            ->groupBy('competencies.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get LOV all Competencies in ONE company
     * @param effBegin, effEnd
     */
    public function getLov($data)
    {
        return
            DB::table('competencies')
                ->select(
                    'id',
                    'code',
                    'name',
                    'type'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()],
                ])
                ->whereNotIn('id',$data)
                ->whereIn('competencies.id', function ($query)  use($data) {
                    $query->select(DB::raw('MAX(competencies.id) as id'))
                            ->from('competencies')
                            ->groupBy('competencies.code')
                            ->get();
                })
                ->get();
    }

    /**
     * Get one competenies based on competenies code
     * @param  $competencyCode
     */
    public function getOne($competencyCode)
    {
        return
            DB::table('competencies')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency as coreCompetency',
                    'eff_begin as effBegin',
                    'provider_id as providerId',
                    'eff_end as effEnd',
                    'lovs.val_data as typeName'
                )
                ->join('lovs', function ($join) {
                    $join
                        ->on('lovs.key_data', '=', 'competencies.type');
                })
                ->where([
                    ['lovs.lov_type_code','COMTYPE'],
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['lovs.tenant_id', $this->requester->getTenantId()],
                    ['lovs.company_id', $this->requester->getCompanyId()],
                    ['code', $competencyCode],
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }


    /**
     * Get one competenies based on competenies code
     * @param  $competencyCode
     */
    public function getOneCompetency($competencyCode)
    {
        return
            DB::table('competencies')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency as coreCompetency',
                    'lovs.val_data as typeName'
                )
                ->join('lovs', function ($join) {
                    $join
                        ->on('lovs.key_data', '=', 'competencies.type');
                })
                ->where([
                    ['lovs.lov_type_code','COMTYPE'],
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['lovs.tenant_id', $this->requester->getTenantId()],
                    ['lovs.company_id', $this->requester->getCompanyId()],
                    ['code', $competencyCode],
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }

    public function getOneById($competencyId) {
        return
            DB::table('competencies')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency as coreCompetency',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['id', $competencyId],
                ])
                ->first();
    }

    /**
     * Get all competencies based on competenies code
     * @param  $competencyCode, $competencyId
     */
    public function getAllByCode($competencyCode, $competencyId)
    {
        return
            DB::table('competencies')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'type',
                    'rating_scale_code as ratingScaleCode',
                    'core_competency as coreCompetency',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'lovs.val_data as typeName'
                )
                ->join('lovs', function ($join) {
                    $join
                        ->on('lovs.key_data', '=', 'competencies.type');
                })
                ->where([
                    ['lovs.lov_type_code','COMTYPE'],
                    ['competencies.tenant_id', $this->requester->getTenantId()],
                    ['competencies.company_id', $this->requester->getCompanyId()],
                    ['lovs.tenant_id', $this->requester->getTenantId()],
                    ['lovs.company_id', $this->requester->getCompanyId()],
                    ['code', $competencyCode],
                    ['id', '!=', $competencyId],
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    /**
     * Insert data competencies to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'competencies', $obj);

        return DB::table('competencies')->insertGetId($obj);
    }

    /**
     * Update data competencies to DB
     * @param  array obj, competencyId
     */
    public function update($competencyId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'competencies', $obj);

        DB::table('competencies')
            ->where([
                ['id', $competencyId]
            ])
            ->update($obj);
    }

    /**
     * Check Duplicate Competency Code in data competencies
     * @param string $code
     * @return
     */
    public function checkDuplicateCompetencyCode(string $code)
    {
        return DB::table('competencies')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    public function getCompetencyGroupByCompetencyCode($competencyCode) {

        $getData = $this->getCompetencyGroupCompetency($competencyCode);
        $tempCompetencyGroupName = [];
        if(count($getData) > 0) {
            for($i = 0 ; $i < count($getData) ; $i++)
            {
                $getDataCompetencyGroup = $this->getLastCompetencyGroup($getData[$i]->competencyGroupId);
                if(!empty($getDataCompetencyGroup)){
                    array_push($tempCompetencyGroupName, $getDataCompetencyGroup);
                }
            }
        }
        return $tempCompetencyGroupName;
    }

    public function getCompetencyGroupCompetency($competencyCode) {
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
                    ['competency_code', $competencyCode]
                ])
                ->get();
    }

    public function getLastCompetencyGroup($competencyGroupId) {

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
                    ['id', $competencyGroupId],
                    ['eff_begin','<=', Carbon::now()],
                    ['eff_end','>=', Carbon::now()]
                ])
                ->whereIn('competency_groups.id', function ($query) {
                    $query->select(DB::raw('MAX(id) as id'))
                            ->from('competency_groups')
                            ->groupBy('code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->first();
    }
}
