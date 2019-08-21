<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

/**
 * @property Requester requester
 */
class PosStructureHierarchyDao
{
    public function __construct(Requester $requester) {
        $this->requester = $requester;
    }
    /**
     * Get hierarchy by pos structure id
     * @param  posStructureId
     */
    public function getRecursive($posStructureId)
    {
        return
            DB::select(
                DB::raw(
                    "WITH RECURSIVE parents AS (
                        (
                            SELECT DISTINCT
                                pos_structure_id as \"posStructureId\",
                                position_code as code,
                                parent_position_code as \"parentCode\",
                                p.name ,
                                u.name as unit,
                                array[position_code || '']::varchar[] as path
                            FROM
                                pos_structure_hierarchies ps, positions p, units u
                            WHERE
                                pos_structure_id = :pos_structure_id AND
                                ps.tenant_id = :tenant_id AND
                                ps.company_id = :company_id AND
                                ps.position_code = p.code AND
                                p.unit_code = u.code AND
                                parent_position_code IS NULL
                        )
                        UNION
                        (
                            SELECT DISTINCT
                                e.\"posStructureId\",
                                e.code,
                                e.\"parentCode\",
                                e.name,
                                e.unit,
                                (parents.path || e.code)
                            FROM
                            (
                                SELECT
                                    pos_structure_id as \"posStructureId\",
                                    position_code as code,
                                    parent_position_code as \"parentCode\",
                                    p.name as name,
                                    u.name as unit
                                FROM
                                    pos_structure_hierarchies ps, positions p, units u
                                WHERE
                                    pos_structure_id = :pos_structure_id AND
                                    ps.tenant_id = :tenant_id AND
                                    ps.company_id = :company_id AND
                                    ps.position_code = p.code AND
                                    p.unit_code = u.code
                            ) e, parents
                            WHERE
                                e.\"parentCode\" = parents.code
                        )
                    )
                    SELECT * FROM parents ORDER BY path;
                    "
                ), [
                'pos_structure_id' => $posStructureId,
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId()
            ]);
    }

    public function getFlat($posStructureId)
    {
        return
            DB::table('pos_structure_hierarchies')
                ->select(
                    'pos_structure_id as posStructureId',
                    'position_code as positionCode',
                    'positions.name as positionName',
                    'parent_position_code as parentPositionCode'
                )
                ->join('positions', 'positions.code', '=', 'position_code')
                ->where([
                    ['pos_structure_hierarchies.tenant_id', $this->requester->getTenantId()],
                    ['pos_structure_hierarchies.company_id', $this->requester->getCompanyId()],
                    ['pos_structure_id', $posStructureId]
                ])
                ->get();
    }

    /**
     * Insert data pos structure hierarchy to DB
     * @param  array obj
     */
    public function save($obj)
    {
        DB::table('pos_structure_hierarchies')->insert($obj);
    }

    /**
     * Delete data pos structure hierarchy from DB
     * @param  id
     */
    public function delete($posStructureId)
    {
        DB::table('pos_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['pos_structure_id', $posStructureId]
            ])->delete();
    }

    public function getParent($posStructureId, $positionCode)
    {
        return
            DB::table('pos_structure_hierarchies')
                ->select('parent_position_code as code')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['pos_structure_id', $posStructureId],
                    ['position_code', $positionCode]
                ])
                ->first();
    }

    public function getChildren($posStructureId, $positionCode)
    {
        return
            DB::table('pos_structure_hierarchies')
                ->select('position_code as code')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['pos_structure_id', $posStructureId],
                    ['parent_position_code', $positionCode]
                ])
                ->get();
    }

    public function getChildrenCount($posStructureId, $positionCode)
    {
        return
            DB::table('pos_structure_hierarchies')
                ->select('position_code as code')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['pos_structure_id', $posStructureId],
                    ['parent_position_code', $positionCode]
                ])
                ->count();
    }

    public function removeNode($posStructureId, $positionCode)
    {
        DB::table('pos_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['pos_structure_id', $posStructureId],
                ['position_code', $positionCode]
            ])
            ->delete();
    }

    public function updateNode($posStructureId, $positionCode, $obj)
    {
        DB::table('pos_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['pos_structure_id', $posStructureId],
                ['position_code', $positionCode]
            ])
            ->update($obj);
    }

    public function updateChildren($posStructureId, $positionCode, $obj)
    {
        DB::table('pos_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['pos_structure_id', $posStructureId],
                ['parent_position_code', $positionCode]
            ])
            ->update($obj);
    }

    public function nodeExists($posStructureId, $positionCode)
    {
        $count = DB::table('pos_structure_hierarchies')->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['pos_structure_id', $posStructureId],
                ['position_code', $positionCode]
            ])->count();
        return $count > 0;
    }
}
