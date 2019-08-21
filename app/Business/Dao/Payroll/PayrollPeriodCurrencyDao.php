<?php
namespace App\Business\Dao\Payroll;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class PayrollPeriodCurrencyDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'payroll';
        $this->requester = $requester;
    }

    public function checkIfCompanyCurrencyIsUsed($companyCurr) {
        return
            DB::connection($this->connection)
                ->table('payroll_period_currency')
                ->select(
                    'lov_curr as lovCurr',
                    'rate'
                )
                ->where([
                    ['payroll_period_currency.tenant_id', $this->requester->getTenantId()],
                    ['payroll_period_currency.company_id', $this->requester->getCompanyId()],
                    ['payroll_period_currency.lov_curr', '=', $companyCurr]
                ])
                ->get();
    }
}