<?php

namespace App\Business\Dao;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;

class OrgStructureHierarchyDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get hierarchy by org structure id
     * @param  tenantId , companyId, orgStructureId
     */
    public function getRecursive($tenantId, $companyId, $orgStructureId)
    {
        $now = Carbon::now();
        $today = Carbon::parse($now)->format('Y-m-d');

        return
            DB::select(
                DB::raw(
                    "
                    WITH RECURSIVE parents AS (
                        (
                            SELECT DISTINCT
                                org_structure_id as \"orgStructureId\",
                                u.code,
                                u.name,
                                a.eff_begin,
                                a.eff_end,
                                ps.is_head as \"hou\",
                                ut.name as \"houUnitTypeName\",
                                p.first_name as \"houFirstName\",
                                p.last_name as \"houLastName\",
                                p.file_photo as \"houPhoto\",
                                ps.code as \"houPositionCode\",
                                ps.name as \"houPositionName\",
                                parent_unit_code as \"parentCode\",
                                array[u.code || '']::varchar[] as path
                            FROM
                                org_structure_hierarchies o, units u
                            LEFT JOIN assignments a ON u.code = a.unit_code
                                AND a.is_primary=true
                                AND a.lov_asta='ACT'
                                AND :now between a.eff_begin and a.eff_end
                                AND a.tenant_id = :tenant_id
                                AND a.company_id = :company_id
                            LEFT JOIN positions ps ON a.position_code = ps.code
                                AND ps.tenant_id = :tenant_id
                                AND ps.company_id = :company_id
                            LEFT JOIN persons p ON a.person_id = p.id
                            LEFT JOIN unit_types ut ON u.unit_type_code = ut.code
                                AND ut.tenant_id = :tenant_id
                                AND ut.company_id = :company_id
                            WHERE
                                org_structure_id = :org_structure_id AND
                                o.tenant_id = :tenant_id AND
                                o.company_id = :company_id AND
                                o.unit_code = u.code AND
                                u.tenant_id = :tenant_id AND
                                u.company_id = :company_id AND
                                parent_unit_code IS NULL
                        )
                        UNION
                        (
                            SELECT DISTINCT
                                e.\"orgStructureId\",
                                e.code,
                                e.name,
                                e.eff_begin,
                                e.eff_end,
                                e.\"hou\",
                                e.\"houUnitTypeName\",
                                e.\"houFirstName\",
                                e.\"houLastName\",
                                e.\"houPhoto\",
                                e.\"houPositionCode\",
                                e.\"houPositionName\",
                                e.\"parentCode\",
                                (parents.path || e.code)
                            FROM
                            (
                                SELECT
                                    org_structure_id as \"orgStructureId\",
                                    u.code,
                                    u.name,
                                    a.eff_begin,
                                    a.eff_end,
                                    ps.is_head as \"hou\",
                                    ut.name as \"houUnitTypeName\",
                                    p.first_name as \"houFirstName\",
                                    p.last_name as \"houLastName\",
                                    p.file_photo as \"houPhoto\",
                                    ps.code as \"houPositionCode\",
                                    ps.name as \"houPositionName\",
                                    parent_unit_code as \"parentCode\"
                                FROM
                                    org_structure_hierarchies o, units u
                                LEFT JOIN assignments a ON u.code = a.unit_code
                                    AND a.is_primary=true
                                    AND a.lov_asta='ACT'
                                    AND :now between a.eff_begin and a.eff_end
                                    AND a.tenant_id = :tenant_id
                                    AND a.company_id = :company_id
                                LEFT JOIN positions ps ON a.position_code = ps.code
                                    AND ps.tenant_id = :tenant_id
                                    AND ps.company_id = :company_id
                                LEFT JOIN persons p ON a.person_id = p.id
                                LEFT JOIN unit_types ut ON u.unit_type_code = ut.code
                                    AND ut.tenant_id = :tenant_id
                                    AND ut.company_id = :company_id
                                WHERE
                                    org_structure_id = :org_structure_id AND
                                    o.tenant_id = :tenant_id AND
                                    o.company_id = :company_id AND
                                    u.tenant_id = :tenant_id AND
                                    u.company_id = :company_id AND
                                    o.unit_code = u.code
                            ) e, parents
                            WHERE
                                e.\"parentCode\" = parents.code
                        )
                    )
                    SELECT * FROM parents ORDER BY path, hou DESC, eff_begin ASC ;
                    "
                ), [
                    'now' => $now,
                    'org_structure_id' => $orgStructureId,
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId
                ]
            );
    }

    public function getWorkflow($companyId, $orgStructureId, $unitCode)
    {
        $tenantId= $this->requester->getTenantId();
        $now = Carbon::now();

        return
            DB::select(
                DB::raw(
                    "
                    WITH RECURSIVE parents AS (
                        (
                            SELECT DISTINCT
                                org_structure_id as \"orgStructureId\",
                                u.code,
                                u.name,
                                a.employee_id,
                                a.eff_begin,
                                a.eff_end,
                                ps.is_head as \"hou\",
                                p.first_name as \"houFirstName\",
                                p.last_name as \"houLastName\",
                                ps.code as \"houPositionCode\",
                                ps.name as \"houPositionName\",
                                parent_unit_code as \"parentCode\",
                                array[u.code || '']::varchar[] as path
                            FROM
                                org_structure_hierarchies o, units u
                            LEFT JOIN assignments a ON u.code = a.unit_code
                                AND a.is_primary=true
                                AND a.lov_asta='ACT'
                                AND :now between a.eff_begin and a.eff_end
                                AND a.tenant_id = :tenant_id
                                AND a.company_id = :company_id
                            LEFT JOIN positions ps ON a.position_code = ps.code
                                AND ps.tenant_id = :tenant_id
                                AND ps.company_id = :company_id
                            LEFT JOIN persons p ON a.person_id = p.id
                            WHERE
                                org_structure_id = :org_structure_id AND
                                o.tenant_id = :tenant_id AND
                                o.company_id = :company_id AND
                                o.unit_code = u.code AND
                                parent_unit_code IS NULL AND
                                (u.code NOT IN (SELECT code FROM units WHERE units.code = a.unit_code) OR
                                ps.is_head = true)
                        )
                        UNION
                        (
                            SELECT DISTINCT
                                e.\"orgStructureId\",
                                e.code,
                                e.name,
                                e.employee_id,
                                e.eff_begin,
                                e.eff_end,
                                e.\"hou\",
                                e.\"houFirstName\",
                                e.\"houLastName\",
                                e.\"houPositionCode\",
                                e.\"houPositionName\",
                                e.\"parentCode\",
                                (parents.path || e.code)
                            FROM
                            (
                                SELECT
                                    org_structure_id as \"orgStructureId\",
                                    u.code,
                                    u.name,
                                    a.employee_id,
                                    a.eff_begin,
                                    a.eff_end,
                                    ps.is_head as \"hou\",
                                    p.first_name as \"houFirstName\",
                                    p.last_name as \"houLastName\",
                                    ps.code as \"houPositionCode\",
                                    ps.name as \"houPositionName\",
                                    parent_unit_code as \"parentCode\"
                                FROM
                                    org_structure_hierarchies o, units u
                                LEFT JOIN assignments a ON u.code = a.unit_code
                                    AND a.is_primary=true
                                    AND a.lov_asta='ACT'
                                    AND :now between a.eff_begin and a.eff_end
                                    AND a.tenant_id = :tenant_id
                                    AND a.company_id = :company_id
                                LEFT JOIN positions ps ON a.position_code = ps.code
                                    AND ps.tenant_id = :tenant_id
                                    AND ps.company_id = :company_id
                                LEFT JOIN persons p ON a.person_id = p.id
                                WHERE
                                    org_structure_id = :org_structure_id AND
                                    o.tenant_id = :tenant_id AND
                                    o.company_id = :company_id AND
                                    o.unit_code = u.code
                            ) e, parents
                            WHERE
                                e.\"parentCode\" = parents.code
                        )
                    )
                    SELECT * FROM parents WHERE :unitCode = ANY (path) ORDER BY path, eff_begin ASC, hou DESC ;
                    "
                ), [
                    'now' => $now,
                    'org_structure_id' => $orgStructureId,
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'unitCode' => $unitCode
                ]
            );
//         to do below where
//              (
//                  u.code NOT IN (SELECT code FROM units WHERE units.code = a.unit_code) OR
//                  ps.is_head = true
//              ) AND
    }

    public function getFlat($orgStructureId)
    {
        return
            DB::table('org_structure_hierarchies')
                ->select(
                    'org_structure_id as orgStructureId',
                    'unit_code as unitCode',
                    'units.name as unitName',
                    'parent_unit_code as parentUnitCode'
                )
                ->join('units', function ($join) {
                    $join->on('unit_code', '=', 'units.code')
                        ->where([
                            ['units.tenant_id', $this->requester->getTenantId()],
                            ['units.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['org_structure_hierarchies.tenant_id', $this->requester->getTenantId()],
                    ['org_structure_hierarchies.company_id', $this->requester->getCompanyId()],
                    ['org_structure_id', $orgStructureId]
                ])
                ->get();
    }

    /**
     * Insert data org structure hierarchy to DB
     * @param  array obj
     */
    public function save($obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'org_structure_hierarchies', $obj);

        DB::table('org_structure_hierarchies')->insert($obj);
    }


    /**
     * Delete data org structure hierarchy from DB
     * @param  orgStructureId
     */
    public function delete($orgStructureId)
    {
        DB::table('org_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['org_structure_id', $orgStructureId]
            ])->delete();
    }

    public function getParent($orgStructureId, $unitCode)
    {
        return
            DB::table('org_structure_hierarchies')
                ->select('parent_unit_code as code')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['org_structure_id', $orgStructureId],
                    ['unit_code', $unitCode]
                ])
                ->first();
    }

    public function getChildren($orgStructureId, $unitCode)
    {
        return
            DB::table('org_structure_hierarchies')
                ->select('unit_code as code')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['org_structure_id', $orgStructureId],
                    ['parent_unit_code', $unitCode]
                ])
                ->get();
    }

    public function getChildrenCount($orgStructureId, $unitCode)
    {
        return
            DB::table('org_structure_hierarchies')
                ->select('unit_code as code')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['org_structure_id', $orgStructureId],
                    ['parent_unit_code', $unitCode]
                ])
                ->count();
    }

    public function removeNode($orgStructureId, $unitCode)
    {
        DB::table('org_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['org_structure_id', $orgStructureId],
                ['unit_code', $unitCode]
            ])
            ->delete();
    }

    public function updateNode($orgStructureId, $unitCode, $obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'org_structure_hierarchies', $obj);

        DB::table('org_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['org_structure_id', $orgStructureId],
                ['unit_code', $unitCode]
            ])
            ->update($obj);
    }

    public function updateChildren($orgStructureId, $unitCode, $obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'org_structure_hierarchies', $obj);

        DB::table('org_structure_hierarchies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['org_structure_id', $orgStructureId],
                ['parent_unit_code', $unitCode]
            ])
            ->update($obj);
    }

    public function nodeExists($orgStructureId, $unitCode)
    {
        $count = DB::table('org_structure_hierarchies')->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['org_structure_id', $orgStructureId],
            ['unit_code', $unitCode]
        ])->count();
        return $count > 0;
    }
}
