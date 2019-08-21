<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NumberFormatDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all employee id format
     * @param  offset , limit
     */
    public function getAll($offset, $limit)
    {
        return
            DB::table('autonumbers')
                ->select(
                    'number_formats.id as id',
                    'code as employeeStatusCode',
                    'employee_statuses.name as statusName',
                    'format as numberFormat',
                    'lovs.val_data as type',
                    'number_formats.lov_nbft as lovNbft',
                    'autonumbers.id as autonumberId',
                    'autonumbers.name as autonumberName'
                )
                ->leftJoin('number_formats', function ($join) {
                    $join->on('autonumbers.tenant_id', '=', 'number_formats.tenant_id');
                    $join->on('autonumbers.company_id', '=', 'number_formats.company_id');
                    $join->on('autonumbers.id', '=', 'number_formats.autonumber_id');
                })
                ->leftJoin('employee_statuses', function ($join) {
                    $join->on('number_formats.tenant_id', '=', 'employee_statuses.tenant_id');
                    $join->on('number_formats.company_id', '=', 'employee_statuses.company_id');
                    $join->on('number_formats.employee_status_code', '=', 'employee_statuses.code')
                        ->where([
                            ['eff_begin', '<=', Carbon::now()],
                            ['eff_end', '>=', Carbon::now()]
                        ]);
                })
                ->leftJoin('lovs', function ($join) {
                    $join->on('lovs.tenant_id', '=', 'number_formats.tenant_id');
                    $join->on('lovs.company_id', '=', 'number_formats.company_id');
                    $join->on('lovs.key_data', '=', 'number_formats.lov_nbft')
                        ->where([
                            ['lovs.lov_type_code', 'NBFT']
                        ]);
                })
                ->where([
                    ['autonumbers.tenant_id', $this->requester->getTenantId()],
                    ['autonumbers.company_id', $this->requester->getCompanyId()],
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('number_formats')
            ->select(
                'number_formats.id',
                'code as employeeStatusCode',
                'employee_statuses.name as statusName',
                'format as numberFormat',
                'autonumber_id as autonumberId'
            )
            ->leftJoin('employee_statuses', function ($join) {
                $join->on('number_formats.tenant_id', '=', 'employee_statuses.tenant_id');
                $join->on('number_formats.company_id', '=', 'employee_statuses.company_id');
                $join->on('number_formats.employee_status_code', '=', 'employee_statuses.code');
            })
//            ->leftJoin('lovs', function ($join) {
//                $join->on('lovs.tenant_id', '=', 'number_formats.tenant_id');
//                $join->on('lovs.company_id', '=', 'number_formats.company_id');
//                $join->on('lovs.lov_type_code', '=', 'NBFT');
//                $join->on('lovs.key_data', '=', 'number_formats.lov_nbft');
//            })
            ->leftJoin('autonumbers', function ($join) {
                $join->on('autonumbers.tenant_id', '=', 'number_formats.tenant_id');
                $join->on('autonumbers.company_id', '=', 'number_formats.company_id');
                $join->on('autonumbers.id', '=', 'number_formats.autonumber_id');
            })
            ->where([
                ['employee_statuses.tenant_id', $this->requester->getTenantId()],
                ['employee_statuses.company_id', $this->requester->getCompanyId()],
                ['eff_begin', '<=', Carbon::now()],
                ['eff_end', '>=', Carbon::now()]
            ])
            ->count();
    }

    /**
     * Get one employee id format based on employeeidformat id
     * @param  $employeeIdFormatId
     */
    public function getOne($employeeIdFormatId)
    {
        return
            DB::table('employee_statuses')
                ->select(
                    'number_formats.id',
                    'code as employeeStatusCode',
                    'format as numberFormat',
                    'number_formats.lov_nbft as lovNbft',
                    'employee_statuses.name as statusName',
                    'autonumber_id as autonumberId'
                )
                ->leftJoin('number_formats', function ($join) {
                    $join->on('number_formats.tenant_id', '=', 'employee_statuses.tenant_id');
                    $join->on('number_formats.company_id', '=', 'employee_statuses.company_id');
                    $join->on('number_formats.employee_status_code', '=', 'employee_statuses.code');
                })
                ->leftJoin('lovs', function ($join) {
                    $join->on('lovs.tenant_id', '=', 'number_formats.tenant_id');
                    $join->on('lovs.company_id', '=', 'number_formats.company_id');
                    $join->on('lovs.key_data', '=', 'number_formats.lov_nbft')
                        ->where([
                            ['lovs.lov_type_code', 'NBFT']
                        ]);
                })
                ->leftJoin('autonumbers', function ($join) {
                    $join->on('autonumbers.tenant_id', '=', 'number_formats.tenant_id');
                    $join->on('autonumbers.company_id', '=', 'number_formats.company_id');
                    $join->on('autonumbers.id', '=', 'number_formats.autonumber_id');
                })
                ->where([
                    ['employee_statuses.tenant_id', $this->requester->getTenantId()],
                    ['employee_statuses.company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->first();
    }

    /**
     * Insert data employee id format to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'number_formats', $obj);

        return DB::table('number_formats')->insertGetId($obj);
    }

    /**
     * Update data employee id format to DB
     * @param  array obj, employeeIdFormatId
     */
    public function update($employeeIdFormatId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'number_formats', $obj);

        DB::table('number_formats')
            ->where([
                ['id', $employeeIdFormatId]
            ])
            ->update($obj);
    }

    /**
     * Delete data employee id format from DB
     * @param  employeeIdFormatId
     */
    public function delete($employeeIdFormatId)
    {
        DB::table('number_formats')->where('id', $employeeIdFormatId)->delete();
    }

    /**
     * @param string $format
     * @return
     */
    public function checkDuplicateEmployeeIdFormat(string $format)
    {
        return DB::table('number_formats')->where([
            ['format', $format],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }


    /**
     * @param string $format
     * @param $id if update data, then check duplicate code beside current user id
     * @return
     */
    public function checkDuplicateEditEmployeeIdFormat(string $format, $id)
    {
        $result = DB::table('number_formats')->where([
            ['format', $format],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($id)) {
            $result->where('id', '!=', $id);
        }

        return $result->count();
    }

}
