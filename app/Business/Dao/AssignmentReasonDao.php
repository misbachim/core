<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class AssignmentReasonDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all assignmentReason for one person
     * @param $companyId
     * @return
     */
    public function getAll($companyId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('assignment_reasons')
                ->select(
                    'assignment_reasons.id',
                    'assignment_reasons.eff_begin as effBegin',
                    'assignment_reasons.eff_end as effEnd',
                    'assignment_reasons.code',
                    'assignment_reasons.description',
                    'assignment_reasons.lov_acty as lovActy',
                    'action_types.val_data as actionType'
                )
                ->distinct()
                ->join('lovs as action_types',  function ($join) use($companyId, $tenantId)  {
                    $join->on('action_types.key_data', '=', 'assignment_reasons.lov_acty')
                        ->where([
                            ['action_types.lov_type_code', 'ACTY'],
                            ['action_types.tenant_id', $tenantId],
                            ['action_types.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['assignment_reasons.tenant_id', $tenantId],
                    ['assignment_reasons.company_id', $companyId],
                ])
                ->get();
    }

    public function getAllActive($companyId)
    {
        return
            DB::table('assignment_reasons')
                ->select(
                    'id',
                    'code',
                    'description',
                    'lov_acty'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->get();
    }

    /**
     * Get one assignmentReason based on assignmentReasonId
     * @param $companyId
     * @param $assignmentReasonId
     * @return
     */
    public function getOne($companyId, $assignmentReasonId)
    {
        return
            DB::table('assignment_reasons')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'description',
                    'lov_acty as lovActy'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['id', $assignmentReasonId]
                ])
                ->first();
    }

    /**
     * Insert data assignmentReason to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignment_reasons', $obj);

        return DB::table('assignment_reasons')->insertGetId($obj);
    }

    /**
     * Update data assignmentReason to DB
     * @param $companyId
     * @param $assignmentReasonId
     * @param $obj
     */
    public function update($companyId, $assignmentReasonId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignment_reasons', $obj);

        DB::table('assignment_reasons')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['id', $assignmentReasonId]
        ])
        ->update($obj);
    }

    /**
     * Delete data assignmentReason from DB.
     * @param $companyId
     * @param $assignmentReasonId
     */
    public function delete($companyId, $assignmentReasonId)
    {
        DB::table('assignment_reasons')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['id', $assignmentReasonId]
        ])
        ->delete();
    }

    public function isCodeDuplicate(string $code)
    {
        $lowerCode = strtolower("%$code%");
        return (DB::table('assignment_reasons')
            ->whereRaw('LOWER(code) like ?', [$lowerCode])
            ->where([
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }
}
