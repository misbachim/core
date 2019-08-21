<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all units in ONE company
     * @param
     */
    public function getAll()
    {
        return
            DB::table('units')
            ->select(
                'units.id',
                'units.eff_begin as effBegin',
                'units.eff_end as effEnd',
                'units.code',
                'units.name',
                'locations.name as locationName',
                'unit_types.name as unitTypeName'
            )
            ->leftJoin('locations', function ($join) {
                $join
                    ->on('locations.code', '=', 'units.location_code')
                    ->on('locations.tenant_id', '=', 'units.tenant_id')
                    ->on('locations.company_id', '=', 'units.company_id');
            })
            ->leftJoin('unit_types', function ($join) {
                $join
                    ->on('unit_types.code', '=', 'units.unit_type_code')
                    ->on('unit_types.tenant_id', '=', 'units.tenant_id')
                    ->on('unit_types.company_id', '=', 'units.company_id');
            })
            ->where([
                ['units.tenant_id', $this->requester->getTenantId()],
                ['units.company_id', $this->requester->getCompanyId()]
            ])
            ->get();
    }



    /**
     * Get all active units in ONE company
     * @param
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('units')
            ->select(
                'units.id',
                'units.eff_begin as effBegin',
                'units.eff_end as effEnd',
                'units.code',
                'units.name',
                'locations.name as locationName',
                'unit_types.name as unitTypeName'
            )
            ->leftJoin('locations', function ($join) {
                $join
                    ->on('locations.code', '=', 'units.location_code')
                    ->on('locations.tenant_id', '=', 'units.tenant_id')
                    ->on('locations.company_id', '=', 'units.company_id');
            })
            ->leftJoin('unit_types', function ($join) {
                $join
                    ->on('unit_types.code', '=', 'units.unit_type_code')
                    ->on('unit_types.tenant_id', '=', 'units.tenant_id')
                    ->on('unit_types.company_id', '=', 'units.company_id');
            })
            ->where([
                ['units.tenant_id', $this->requester->getTenantId()],
                ['units.company_id', $this->requester->getCompanyId()],
                ['units.eff_begin', '<=', Carbon::now()],
                ['units.eff_end', '>=', Carbon::now()]
            ])
            ->orderByRaw('units.eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    public function getAllInActive()
    {
        return
            DB::table('units')
            ->select(
                'units.id',
                'units.eff_begin as effBegin',
                'units.eff_end as effEnd',
                'units.code',
                'units.name',
                'locations.name as locationName',
                'unit_types.name as unitTypeName'
            )
            ->leftJoin('locations', function ($join) {
                $join
                    ->on('locations.code', '=', 'units.location_code')
                    ->on('locations.tenant_id', '=', 'units.tenant_id')
                    ->on('locations.company_id', '=', 'units.company_id');
            })
            ->leftJoin('unit_types', function ($join) {
                $join
                    ->on('unit_types.code', '=', 'units.unit_type_code')
                    ->on('unit_types.tenant_id', '=', 'units.tenant_id')
                    ->on('unit_types.company_id', '=', 'units.company_id');
            })
            ->where([
                ['units.tenant_id', $this->requester->getTenantId()],
                ['units.company_id', $this->requester->getCompanyId()],
                ['units.eff_end', '<', Carbon::now()]
            ])
            ->get();
    }


    /**
     * Get one unit in ONE company based on unit id
     * @param  unitId
     */
    public function getOne($unitCode)
    {
        return
            DB::table('units')
            ->select(
                'units.eff_begin as effBegin',
                'units.eff_end as effEnd',
                'units.code',
                'units.name',
                'location_code as locationCode',
                'locations.name as locationName',
                'locations.address as locationAddress',
                'cost_center_code as costCenterCode',
                'cost_centers.name as costCenterName',
                'unit_type_code as unitTypeCode',
                'unit_types.name as unitTypeName'
            )
            ->leftJoin('locations', function ($join) {
                $join
                    ->on('locations.code', '=', 'units.location_code');
            })
            ->leftJoin('cost_centers', function ($join) {
                $join
                    ->on('cost_centers.code', '=', 'units.cost_center_code');
            })
            ->leftJoin('unit_types', function ($join) {
                $join
                    ->on('unit_types.code', '=', 'units.unit_type_code');
            })
            ->where([
                ['units.tenant_id', $this->requester->getTenantId()],
                ['units.company_id', $this->requester->getCompanyId()],
                ['units.code', $unitCode]
            ])
            ->first();
    }

    /**
     * get one data for a unit
     * this function is used in education institution and
     * specialization Used By Employee
     * @param  string $unitCode
     */
    public function getOneUnitByCode($unitCode)
    {
        return
            DB::table('units')
            ->select(
                'units.id',
                'units.eff_begin as effBegin',
                'units.eff_end as effEnd',
                'units.code',
                'units.name'
            )
            ->where([
                ['units.tenant_id', $this->requester->getTenantId()],
                ['units.company_id', $this->requester->getCompanyId()],
                ['units.code', $unitCode]
            ])
            ->first();
    }

    /**
     * Insert data unit to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'units', $obj);

        return DB::table('units')->insertGetId($obj);
    }

    /**
     * Update data unit to DB
     * @param  unitId, array obj
     */
    public function update($unitCode, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'units', $obj);

        DB::table('units')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['code', $unitCode]
            ])
            ->update($obj);
    }

    /**
     * Delete data unit from DB
     * @param  unitId
     */
    public function delete($code)
    {
        DB::table('units')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->delete();
    }

    public function getSLov($menuCode)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $roleIds = $this->requester->getRoleIds();

        $roleIds_param = 'array[' . implode(",", $roleIds) . ']';

        $query = DB::table(DB::raw('f_uni_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'))
            ->select(
                'unit_code as code',
                'unit_name as name'
            );

        return $query->get();
    }

    /**
     * @param string code
     * @return
     */
    public function checkDuplicateUnitCode(string $code)
    {
        return DB::table('units')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    public function getTotalRows()
    {
        return
            DB::table('units')
            ->leftJoin('locations', 'locations.code', '=', 'units.location_code')
            ->leftJoin('unit_types', 'unit_types.code', '=', 'units.unit_type_code')
            ->where([
                ['units.tenant_id', $this->requester->getTenantId()],
                ['units.company_id', $this->requester->getCompanyId()]
            ])->count();
    }

    public function search($query, $offset, $limit)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        return
            DB::table('units')
            ->select('code', 'name')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_begin', '<=', $now],
                ['eff_end', '>=', $now]
            ])
            ->whereRaw('LOWER(name) like ?', [$searchString])
            ->get();
    }

    public function searchCustom($searchQuery, $param)
    {
        $query = null;

        if ($param !== 'id') {
            $searchString = strtolower("%$searchQuery%");
            $query = DB::table('units')
                ->select(
                    'id',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', $now],
                    ['eff_end', '>=', $now]
                ])
                ->whereRaw('LOWER(' . $param . ') like ?', [$searchString])
                ->get();
        } else {
            $query = DB::table('units')
                ->select(
                    'id',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', $now],
                    ['eff_end', '>=', $now]
                ])
                ->get();
        }

        return $query;
    }
}
