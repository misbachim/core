<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PositionDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all position in ONE company
     */
    public function getAll($offset, $limit)
    {
        return
            DB::table('positions')
            ->select(
                'positions.code',
                'positions.name',
                'positions.description',
                'positions.eff_begin as effBegin',
                'positions.eff_end as effEnd',
                'positions.is_head as isHead',
                'unit_code as unitCode',
                'units.name as unitName',
                'job_code as jobCode',
                'jobs.name as jobName'
            )
            ->leftJoin('units', function ($join) {
                $join
                    ->on('units.code', '=', 'positions.unit_code')
                    ->on('units.tenant_id', '=', 'positions.tenant_id')
                    ->on('units.company_id', '=', 'positions.company_id');
            })
            ->leftJoin('jobs', function ($join) {
                $join
                    ->on('jobs.code', '=', 'positions.job_code')
                    ->on('jobs.tenant_id', '=', 'positions.tenant_id')
                    ->on('jobs.company_id', '=', 'positions.company_id');
            })
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()]
            ])
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get all position in ONE company
     */
    public function getQuantityByUnit($unit)
    {
        return
            DB::table('positions')
            ->select()
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()],
                ['positions.unit_code', $unit]
            ])
            ->get();
    }

    /**
     * Get All Active grade
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('positions')
            ->select(
                'positions.code',
                'positions.name',
                'positions.description',
                'positions.eff_begin as effBegin',
                'positions.eff_end as effEnd',
                'positions.is_head as isHead',
                'unit_code as unitCode',
                'units.name as unitName',
                'job_code as jobCode',
                'jobs.name as jobName'
            )
            ->leftJoin('units', function ($join) {
                $join
                    ->on('units.code', '=', 'positions.unit_code')
                    ->on('units.tenant_id', '=', 'positions.tenant_id')
                    ->on('units.company_id', '=', 'positions.company_id');
            })
            ->leftJoin('jobs', function ($join) {
                $join
                    ->on('jobs.code', '=', 'positions.job_code')
                    ->on('jobs.tenant_id', '=', 'positions.tenant_id')
                    ->on('jobs.company_id', '=', 'positions.company_id');
            })
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()],
                ['positions.eff_begin', '<=', Carbon::now()],
                ['positions.eff_end', '>=', Carbon::now()]
            ])
            ->orderByRaw('positions.eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get All InActive
     */
    public function getAllInActive()
    {
        return
            DB::table('positions')
            ->select(
                'positions.code',
                'positions.name',
                'positions.description',
                'positions.eff_begin as effBegin',
                'positions.eff_end as effEnd',
                'positions.is_head as isHead',
                'unit_code as unitCode',
                'units.name as unitName',
                'job_code as jobCode',
                'jobs.name as jobName'
            )
            ->leftJoin('units', function ($join) {
                $join
                    ->on('units.code', '=', 'positions.unit_code')
                    ->on('units.tenant_id', '=', 'positions.tenant_id')
                    ->on('units.company_id', '=', 'positions.company_id');
            })
            ->leftJoin('jobs', function ($join) {
                $join
                    ->on('jobs.code', '=', 'positions.job_code')
                    ->on('jobs.tenant_id', '=', 'positions.tenant_id')
                    ->on('jobs.company_id', '=', 'positions.company_id');
            })
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()],
                ['positions.eff_end', '<', Carbon::now()]
                ])
            ->get();
    }

    public function getLov($unitCode, $jobCode = null)
    {
        $query = DB::table('positions')
                ->select(
                    'code',
                    'name',
                    'is_head as isHead'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ]);

        if ($unitCode) {
            $query->where('unit_code', $unitCode);
        }

        if ($jobCode) {
            $query->where('job_code', $jobCode);
        }

        return $query->get();
    }

    public function getTotalRows()
    {
        return
            DB::table('positions')
            ->leftJoin('units', 'units.code', '=', 'positions.unit_code')
            ->leftJoin('jobs', 'jobs.code', '=', 'positions.job_code')
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()]
            ])->count();
    }

    /**
     * Get one position based on position code
     */
    public function getOne($positionCode)
    {
        return
            DB::table('positions')
            ->select(
                'positions.code',
                'positions.name',
                'positions.description',
                'positions.eff_begin as effBegin',
                'positions.eff_end as effEnd',
                'positions.unit_code as unitCode',
                'units.name as unitName',
                'positions.job_code as jobCode',
                'jobs.name as jobName',
                'positions.is_head as isHead'
            )
            ->leftjoin('units', 'units.code', '=', 'positions.unit_code')
            ->leftjoin('jobs', 'jobs.code', '=', 'positions.job_code')
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()],
                ['positions.code', $positionCode]
            ])
            ->first();
    }

    /**
     * get one data for a position
     * this function is used in education institution and
     * specialization Used By Employee and workflow to get name
     * @param  string $positionCode
     */
    public function getOnePositionByCode($positionCode)
    {
        return
            DB::table('positions')
            ->select(
                'positions.code',
                'positions.name',
                'positions.description',
                'unit_code as unitCode',
                'job_code as jobCode'
            )
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()],
                ['positions.code', $positionCode]
            ])
            ->first();
    }

    /**
     * Insert data position to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'positions', $obj);

        return DB::table('positions')->insert($obj);
    }

    /**
     * Update data position to DB
     * @param  array obj, tenantId, companyId, positionId
     */
    public function update($positionCode, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'positions', $obj);

        DB::table('positions')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['code', $positionCode]
            ])
            ->update($obj);
    }

    public function getSLov($menuCode, $unitCode, $jobCode)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $roleIds = $this->requester->getRoleIds();

        $roleIds_param = 'array[' . implode(",", $roleIds) . ']';

        $query = DB::table(DB::raw('f_pos_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'))
            ->select(
                'f_pos_lovs.position_code as code',
                'f_pos_lovs.position_name as name',
                'positions.is_head as isHead'
            )
            ->join('positions', 'positions.code', 'f_pos_lovs.position_code')
            ->where([
                ['positions.eff_begin', '<=', Carbon::now()],
                ['positions.eff_end', '>=', Carbon::now()],
                ['positions.unit_code', $unitCode],
                ['positions.job_code', $jobCode]
            ]);

        return $query->get();
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('positions')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count() > 0);
    }

    public function isFull($companyId, $code)
    {
        $now = Carbon::now();
        $count = DB::table('positions')
            ->join('assignments', 'assignments.position_code', '=', 'positions.code')
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $companyId],
                ['positions.code', $code],
                ['positions.is_single', true],
                ['assignments.eff_begin', '<=', $now],
                ['assignments.eff_end', '>=', $now]
            ])
            ->count();
        return ($count >= 1);
    }

    public function search($query, $offset, $limit)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        return
            DB::table('positions')
            ->select('code', 'name')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_begin', '<=', $now],
                ['eff_end', '>=', $now]
            ])
            ->whereRaw('LOWER(name) like ?', [$searchString])
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * check if position isHead is exist
     */
    public function checkHeadOfUnitOnUnit($unitCode)
    {
        return
            DB::table('positions')
            ->select(
                'code',
                'name'
            )
            ->where([
                ['positions.tenant_id', $this->requester->getTenantId()],
                ['positions.company_id', $this->requester->getCompanyId()],
                ['positions.unit_code', $unitCode],
                ['positions.is_head', true]
            ])
            ->get();
    }

    /*
    |-----------------------------
    | get all data ke database
    |-----------------------------
    |
    |
    */
    public function getPositionForMultipleSelect($param) {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return DB::table('positions')
            ->select(
                'id',
                'name',
                'code'
            )
        ->where([
            ['company_id', $companyId],
            ['tenant_id', $tenantId]
         ])
         ->whereNotIn('code', $param)
         ->get();
    }
}
