<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

/**
 * @property Requester requester
 */
class NumberFormatsDocumentDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function getAssignmentDocument($lovNbft)
    {
        return
            DB::table('number_formats')
                ->select(
                    'autonumbers.id as autonumbersId',
                    'number_formats.id as id',
                    'format as employeeFormat',
                    'starting_sequence',
                    'last_sequence'
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
                ->first();
    }
}
