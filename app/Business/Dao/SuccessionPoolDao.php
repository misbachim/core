<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuccessionPoolDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /*
    |-----------------------------
    | get all  data from database
    |-----------------------------
    | @param $companyId <integer>
    |
    */
    public function getAll($companyId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('succession_pools')
            ->select(
                'id',
                'position_code as positionCode',
                'key',
                'critical',
                'reason',
                'interim'
            )
            ->where([
                ['tenant_id', $tenantId],
                ['company_id', $companyId]
            ])
            ->orderBy('id', 'DESC')
            ->get();
    }

    /*
    |-----------------------------
    | get one data from database
    |-----------------------------
    |
    */
    public function getOne ($id)
    {
        return
            DB::table('succession_pools')
                ->select(
                    'id',
                    'position_code as positionCode',
                    'key',
                    'critical',
                    'reason',
                    'interim'
                )
                ->where([
                    ['id', $id]
                ])
                ->first();
    }

    /*
    |-----------------------------
    | get data employee with offset and limit
    |-----------------------------
    |
    */
    public function getEmployeeNotInSuccession($offset, $limit, $manyEmployeeId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId  = $this->requester->getTenantId();

        $query = DB::table('assignments')
                    ->selectRaw(
                        '(CONCAT(persons.first_name,\' \', persons.last_name)) as "employeeName",'.
                        'persons.file_photo as "filePhoto",'.
                        'assignments.position_code as "positionCode",'.
                        'assignments.employee_id as "employeeId",'.
                        'positions.name as "positionName"'
                    )
                    ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                        $join->on('assignments.position_code', '=', 'positions.code')
                            ->where([
                                ['positions.tenant_id', $tenantId],
                                ['positions.company_id', $companyId]
                            ]);
                    })
                    ->leftjoin('persons', function ($join) use ($companyId, $tenantId) {
                        $join->on('persons.id', '=', 'assignments.person_id')
                            ->where([
                                ['persons.tenant_id', $tenantId]
                            ]);
                    })
                    ->where([
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId],
                        ['assignments.lov_asta', 'ACT'],
                        ['assignments.eff_begin', '<=', Carbon::now()],
                        ['assignments.eff_end', '>=', Carbon::now()]
                    ])
                    ->whereNotIn('assignments.employee_id', $manyEmployeeId)
                    ->offset($offset)
                    ->limit($limit);

        return $query->get();
    }

    /*
    |-----------------------------
    | get TotalRows employee
    |-----------------------------
    |
    */
    public function getTotalRowsEmployeeNotInSuccession($manyEmployeeId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId  = $this->requester->getTenantId();

        $query = DB::table('assignments')
            ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.position_code', '=', 'positions.code')
                    ->where([
                        ['positions.tenant_id', $tenantId],
                        ['positions.company_id', $companyId]
                    ]);
            })
            ->leftjoin('persons', function ($join) use ($companyId, $tenantId) {
                $join->on('persons.id', '=', 'assignments.person_id')
                    ->where([
                        ['persons.tenant_id', $tenantId]
                    ]);
            })
            ->where([
                ['assignments.tenant_id', $tenantId],
                ['assignments.company_id', $companyId],
                ['assignments.lov_asta', 'ACT'],
                ['assignments.eff_begin', '<=', Carbon::now()],
                ['assignments.eff_end', '>=', Carbon::now()]
            ])
            ->whereNotIn('assignments.employee_id', $manyEmployeeId);

        return $query->count();
    }



    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    | @param obj dari controller
    |
    */
    public function save ($obj) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        DB::table('succession_pools')->insert($obj);
    }


    /*
    |-----------------------------
    | update data ke database
    |-----------------------------
    |
    |
    */
    public function update ($obj, $id) {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('succession_pools')
        ->where([
            ['id', $id],
         ])
         ->update($obj);
    }

}
