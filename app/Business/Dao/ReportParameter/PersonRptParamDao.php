<?php

namespace App\Business\Dao\ReportParameter;

use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;

class PersonRptParamDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }



    /**
     * Get all Persons First Name.
     */
    public function getAllFirstName()
    {
        $query = 'SELECT DISTINCT persons.id, persons.first_name, assignments.employee_id AS "EmployeeId" from persons '.
                 'JOIN assignments ON persons.id = assignments.person_id '.
                 'WHERE current_date >= persons.eff_begin AND current_date <= persons.eff_end'.
                 ' AND current_date >= assignments.eff_begin AND current_date <= assignments.eff_end'.
                 ' AND persons.tenant_id ='.$this->requester->getTenantId().
                 ' AND assignments.tenant_id ='.$this->requester->getTenantId().
                 ' AND assignments.company_id ='.$this->requester->getCompanyId().

        '';
        return DB::select($query);
    }
    /**
     * Get all Persons Last Name.
     */
    public function getAllLastName()
    {
        $query = 'SELECT id, last_name from persons '.
                 'WHERE current_date >= eff_begin AND current_date <= eff_end';
        return DB::select($query);
    }

    /**
     * Gel all Persons Id.
     */
    public function getAllId(){
        $query = 'SELECT id '.
                 'WHERE current_date >= eff_begin AND current_date <= eff_end';
        return DB::select($query);
    }


}
