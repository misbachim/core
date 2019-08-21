<?php
namespace App\Business\Dao\Payroll;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class FamilyBeneficiariesDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'payroll';
        $this->requester = $requester;
    }

    public function getAllFamilyRelationship($benefitGroupsBenefitsId)
    {
        return
            DB::connection($this->connection)
            ->table('family_beneficiaries')
            ->select(
                'id',
                'benefit_groups_benefits_id as benefitGroupsBenefitsId',
                'lov_famr as lovFamr',
                'age_limit as ageLimit'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['benefit_groups_benefits_id', $benefitGroupsBenefitsId]
            ])
            ->get();
    }
}
