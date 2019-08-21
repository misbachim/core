<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

/**
 * @property Requester requester
 */
class EmployeeIdDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function getEmployeeId($code, $lovNbft)
    {
        return
            DB::table('number_formats')
                ->select(
                    'number_formats.id as numberFormatId',
                    'autonumbers.id as autonumberId',
                    'employee_status_code as employeeStatusCode',
                    'format',
                    'starting_sequence as startingSequence',
                    'last_sequence as lastSequence',
                    'lov_nbft as lovNbft'
                )
                ->join('autonumbers', function ($join) {
                    $join->on('autonumbers.tenant_id', '=', 'number_formats.tenant_id');
                    $join->on('autonumbers.company_id', '=', 'number_formats.company_id');
                    $join->on('autonumbers.id', '=', 'number_formats.autonumber_id');
                })
                ->where([
                    ['number_formats.tenant_id', $this->requester->getTenantId()],
                    ['number_formats.company_id', $this->requester->getCompanyId()],
                    ['number_formats.lov_nbft', $lovNbft],
                    ['number_formats.employee_status_code', $code]
                ])
                ->first();
    }

    public function getEmployeeIdNullEmployeeStatus($lovNbft)
    {
        return
            DB::table('number_formats')
                ->select(
                    'number_formats.id as numberFormatId',
                    'autonumbers.id as autonumberId',
                    'employee_status_code as employeeStatusCode',
                    'format',
                    'starting_sequence as startingSequence',
                    'last_sequence as lastSequence',
                    'lov_nbft as lovNbft'
                )
                ->join('autonumbers', function ($join) {
                    $join->on('autonumbers.tenant_id', '=', 'number_formats.tenant_id');
                    $join->on('autonumbers.company_id', '=', 'number_formats.company_id');
                    $join->on('autonumbers.id', '=', 'number_formats.autonumber_id');
                })
                ->where([
                    ['number_formats.tenant_id', $this->requester->getTenantId()],
                    ['number_formats.company_id', $this->requester->getCompanyId()],
                    ['number_formats.lov_nbft', $lovNbft]
                ])
                ->whereNull('number_formats.employee_status_code')
                ->first();
    }
}
