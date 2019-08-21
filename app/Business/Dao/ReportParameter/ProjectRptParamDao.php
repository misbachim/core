<?php

namespace App\Business\Dao\ReportParameter;
use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;

class ProjectRptParamDao {
    public function __construct(Requester $requester) {
        $this->requester = $requester;
    }

    public function getAllName() {
        $query =   'SELECT DISTINCT NAME 
                    FROM   projects 
                    WHERE  tenant_id = '.$this->requester->getTenantId()
                    . 'AND company_id = '.$this->requester->getCompanyId()
                    . 'AND eff_begin <= current_date 
                       AND eff_end >= current_date 
                    ORDER  BY NAME ';
        return DB::select($query);
    }
}
