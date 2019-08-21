<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function getOrgStructure()
    {
      return DB::table('org_structures')
                  ->select('name','id')
                  ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['is_primary', true]
                  ])
                  ->get();
    }

    public function getOrgStructureHierarchies($request, $id)
    {
      return DB::table('org_structure_hierarchies')
                ->select('units.code as unitCode','units.name as name','org_structure_hierarchies.org_structure_id as orgId','org_structure_hierarchies.parent_unit_code')

                ->join('units', function($j){
                  $j->on('org_structure_hierarchies.unit_code', 'units.code')
                    ->where(function($w){
                      $w->where([
                        ['units.tenant_id', $this->requester->getTenantId()],
                        ['units.company_id', $this->requester->getCompanyId()]
                      ]);
                    });
                  })

                ->where([
                   ['org_structure_hierarchies.tenant_id', $this->requester->getTenantId()],
                   ['org_structure_hierarchies.company_id', $this->requester->getCompanyId()],
                   ['org_structure_hierarchies.org_structure_id', $id],
                   ['org_structure_hierarchies.parent_unit_code', '=', null]
                 ])
                 ->get();
    }

    public function getTotalEmployee($request, $unitCode, $orgId)
    {
        $sql = DB::table('assignments')
                    ->select(DB::raw("count('assignments.id') as total"))
                    ->join('org_structure_hierarchies', function($j) use($orgId, $unitCode){
                      $j->on('org_structure_hierarchies.unit_code', 'assignments.unit_code')
                      ->where([
                         ['org_structure_hierarchies.tenant_id', $this->requester->getTenantId()],
                         ['org_structure_hierarchies.company_id', $this->requester->getCompanyId()],
                         ['org_structure_hierarchies.org_structure_id', $orgId],
                         ['org_structure_hierarchies.unit_code', $unitCode]
                       ]);
                    })
                    ->where([
                       ['assignments.tenant_id', $this->requester->getTenantId()],
                       ['assignments.company_id', $this->requester->getCompanyId()],
                       ['assignments.unit_code', $unitCode],
                       ['assignments.lov_acty', $request->selected]
                     ]);
                   if ($request->selected == 'HIRE') {
                     $sql->whereYear('assignments.eff_begin', $request->year)
                          ->wheremonth('assignments.eff_begin', $request->month);
                   }else{
                     $sql->whereYear('assignments.eff_end', $request->year)
                          ->wheremonth('assignments.eff_end', $request->month);
                   }
      return $sql->get();
    }


    public function getChildOrgStructureHierarchies($unitCode, $orgId)
    {
      return $sql = DB::table('org_structure_hierarchies')
                  ->select(DB::raw("count('org_structure_hierarchies.org_structure_id') as child"))
                  ->join('org_structures', function($j) {
                      $j->on('org_structure_hierarchies.org_structure_id', 'org_structures.id')
                      ->where(function($w) {
                          $w->where([
                            ['org_structures.tenant_id', $this->requester->getTenantId()],
                            ['org_structures.company_id', $this->requester->getCompanyId()],
                            ['org_structures.is_primary', true]
                          ]);
                      });
                  })
                  ->where([
                    ['org_structure_hierarchies.org_structure_id', $orgId],
                    ['org_structure_hierarchies.parent_unit_code', $unitCode]
                  ])
                  ->get();
    }


    public function getOrgStructureHierarchiesChild($request)
    {
      return DB::table('org_structure_hierarchies')
                ->select('units.code as unitCode','units.name as name','org_structure_hierarchies.org_structure_id as orgId','org_structure_hierarchies.parent_unit_code')

                ->join('units', function($j){
                  $j->on('org_structure_hierarchies.unit_code', 'units.code')
                    ->where(function($w){
                      $w->where([
                        ['units.tenant_id', $this->requester->getTenantId()],
                        ['units.company_id', $this->requester->getCompanyId()]
                      ]);
                    });
                  })

                ->where([
                   ['org_structure_hierarchies.tenant_id', $this->requester->getTenantId()],
                   ['org_structure_hierarchies.company_id', $this->requester->getCompanyId()],
                   ['org_structure_hierarchies.org_structure_id', $request->id]
                   // ['org_structure_hierarchies.parent_unit_code', $request->unitCode]
                 ])
                 ->Where([
                   ['org_structure_hierarchies.unit_code', $request->unitCode],
                   ['org_structure_hierarchies.parent_unit_code', '=', null]
                 ])
                 ->orWhere([
                   ['org_structure_hierarchies.parent_unit_code', $request->unitCode],
                   ['org_structure_hierarchies.unit_code', '!=', $request->unitCode]
                 ])
                 ->get();
    }

    public function getActiveStructureHierarchies()
    {
      return DB::table('org_structure_hierarchies')
                  ->select('units.name', 'units.code')

                    ->join('org_structures', function($j) {
                      $j->on('org_structure_hierarchies.org_structure_id', 'org_structures.id')
                          ->where(function($w) {
                              $w->where([
                                ['org_structures.tenant_id', $this->requester->getTenantId()],
                                ['org_structures.company_id', $this->requester->getCompanyId()],
                                ['org_structures.is_primary', true]
                              ]);
                          });
                    })

                    ->join('units', function($j) {
                      $j->on('org_structure_hierarchies.unit_code', 'units.code')
                          ->where(function($w) {
                            $w->where([
                              ['units.tenant_id', $this->requester->getTenantId()],
                              ['units.company_id', $this->requester->getCompanyId()],
                            ]);
                          });
                    })

                  ->where([
                    ['org_structure_hierarchies.tenant_id', $this->requester->getTenantId()],
                    ['org_structure_hierarchies.company_id', $this->requester->getCompanyId()]
                  ])
                  ->get();
    }

    public function getOneForPieChart($request)
    {
      return DB::table('org_structure_hierarchies')
                  ->select('unit_code', 'parent_unit_code')
                  ->where([
                    ['org_structure_hierarchies.tenant_id', $this->requester->getTenantId()],
                    ['org_structure_hierarchies.company_id', $this->requester->getCompanyId()],
                    ['org_structure_hierarchies.unit_code', $request->code],
                    ['org_structure_hierarchies.org_structure_id', $request->id]
                  ])
                  ->first();
    }

}
