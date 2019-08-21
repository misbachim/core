<?php
namespace App\Business\Dao\Payroll;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class PayrollGroupDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'payroll';
        $this->requester = $requester;
    }

    public function getPayrollGroup($code) {
        return
            DB::table('payroll_groups')
                ->select('name')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $code]
                ])
                ->first();
    }
}
