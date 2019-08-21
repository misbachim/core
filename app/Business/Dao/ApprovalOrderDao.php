<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApprovalOrderDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Approval Order for one company
     * @param
     */
    public function getAllApprovalOrder($workflowId, $companyId)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('approval_orders')
                ->select(
                    'workflow_id as workflowId',
                    'lov_wapt as lovWapt',
                    'value',
                    'number',
                    'val_data as valData'
                )
                ->join('lovs as wapt', function ($join) use($companyId, $tenantId)  {
                    $join->on('wapt.key_data', '=', 'approval_orders.lov_wapt')
                        ->where([
                            ['wapt.tenant_id', $tenantId],
                            ['wapt.company_id', $companyId],
                            ['wapt.lov_type_code', 'WAPT']
                        ]);
                })
                ->where([
                    ['approval_orders.tenant_id', $tenantId],
                    ['approval_orders.company_id', $companyId],
                    ['approval_orders.workflow_id', $workflowId]
                ])
                ->orderBy('workflow_id')
                ->orderBy('number')
                ->get();

    }

    /**
     * Get Many Approval Order for one company
     * @param
     */
    public function getManyApprovalOrder(array $workflowId, $companyId)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('approval_orders')
                ->select(
                    'workflow_id as workflowId',
                    'lov_wapt as lovWapt',
                    'value',
                    'number',
                    'val_data as valData'
                )
                ->join('lovs as wapt', function ($join) use($companyId, $tenantId)  {
                    $join->on('wapt.key_data', '=', 'approval_orders.lov_wapt')
                        ->where([
                            ['wapt.tenant_id', $tenantId],
                            ['wapt.company_id', $companyId],
                            ['wapt.lov_type_code', 'WAPT']
                        ]);
                })
                ->where([
                    ['approval_orders.tenant_id', $tenantId],
                    ['approval_orders.company_id', $companyId],
                ])
                ->whereIn('approval_orders.workflow_id', $workflowId)
                ->orderBy('workflow_id')
                ->orderBy('number')
                ->get();
    }


    /**
     * Save data approval orders
     * @param  array obj
     */
    public function saveApprovalOrder($obj)
    {
        return DB::table('approval_orders')->insert($obj);
    }

    /**
     * Delete data approval orders
     * @param  array obj, workflowId
     */
    function deleteApprovalOrder($workflowId, $companyId)
    {
        DB::table('approval_orders')
            ->where([
                ['approval_orders.tenant_id', $this->requester->getTenantId()],
                ['approval_orders.company_id', $companyId],
                ['workflow_id', $workflowId]
            ])
            ->delete();
    }

}
