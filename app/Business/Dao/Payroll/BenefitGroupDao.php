<?php
namespace App\Business\Dao\Payroll;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class BenefitGroupDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'payroll';
        $this->requester = $requester;
    }

    public function getBenefitGroup($code) {
        return
            DB::table('benefit_groups')
                ->select('name')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $code]
                ])
                ->first();
    }
}
