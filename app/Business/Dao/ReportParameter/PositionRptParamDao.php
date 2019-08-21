<?php

namespace App\Business\Dao\ReportParameter;

use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;

class PositionRptParamDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }



    /**
     * Get all Code, Name.
     */
    public function getAllName()
    {
        $query = 'SELECT code, name from positions '.
                 'WHERE current_date >= eff_begin AND current_date <= eff_end'.
                 ' AND tenant_id ='.$this->requester->getTenantId().
                 ' AND company_id ='.$this->requester->getCompanyId().
        '';
        return DB::select($query);
    }



}
