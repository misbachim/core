<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PositionSlotDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all position slot in ONE company
     */
    public function getAll($positionCode)
    {
        return
            DB::table('position_slots')
            ->select(
                'position_slots.code',
                'position_slots.id',
                'position_slots.position_code as positionCode',
                'position_slots.eff_begin as effBegin',
                'position_slots.eff_end as effEnd',
                'positions.name as positionName'
            )
            ->leftJoin('positions', 'positions.code', '=', 'position_slots.position_code')
            ->where([
                ['position_slots.position_code', $positionCode],
                ['position_slots.tenant_id', $this->requester->getTenantId()],
                ['position_slots.company_id', $this->requester->getCompanyId()]
            ])
            ->get();
    }

    public function countAllRows($positionCode)
    {
        return
            DB::table('position_slots')
            ->leftJoin('positions', 'positions.code', '=', 'position_slots.position_code')
            ->where([
                ['position_slots.position_code', $positionCode],
                ['position_slots.tenant_id', $this->requester->getTenantId()],
                ['position_slots.company_id', $this->requester->getCompanyId()]
            ])->count();
    }

    public function getAllActive()
    {
        return
            DB::table('position_slots')
            ->select(
                'code',
                'name'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_begin', '<=', Carbon::now()],
                ['eff_end', '>=', Carbon::now()]
            ])
            ->get();
    }


    /**
     * Get one position based on position code
     */
    public function getOne($positionSlotCode)
    {
        return
            DB::table('position_slots')
            ->select(
                'position_slots.code',
                'position_slots.id',
                'position_slots.position_code as positionCode',
                'position_slots.eff_begin as effBegin',
                'position_slots.eff_end as effEnd',
                'positions.name as positionName'
            )
            ->leftJoin('positions', 'positions.code', '=', 'position_slots.position_code')
            ->where([
                ['position_slots.tenant_id', $this->requester->getTenantId()],
                ['position_slots.company_id', $this->requester->getCompanyId()],
                ['position_slots.code', $positionSlotCode]
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

        LogDao::insertLogImpact($this->requester->getLogId(), 'position_slots', $obj);

        return DB::table('position_slots')->insert($obj);
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
                'f_pos_lovs.position_name as name'
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
}
