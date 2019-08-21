<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PositionCredentialDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all credentials from DB for a position.
     * @param jobId
     */
    public function getAll($positionCode)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('position_credentials')
                ->select(
                    'position_credentials.id',
                    'position_code as positionCode',
                    'credential_code as credentialCode',
                    'credentials.name as credentialName',
                    'credentials.description',
                    
                    'position_credentials.eff_begin as effBegin',
                    'position_credentials.eff_end as effEnd'
                )
                ->leftJoin('credentials', function ($join) use($companyId, $tenantId)  {
                    $join->on('credentials.code', '=', 'position_credentials.credential_code')
                        ->where([
                            ['credentials.tenant_id', $tenantId],
                            ['credentials.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['position_credentials.tenant_id', $tenantId],
                    ['position_credentials.company_id', $companyId],
                    ['position_credentials.position_code', $positionCode],
                    ['position_credentials.eff_begin', '<=', Carbon::now()],
                    ['position_credentials.eff_end', '>=', Carbon::now()]
                ])
                ->whereIn('credentials.id', function($query) {
                    $query->select(DB::raw('max(id) as id'))
                          ->from('credentials')
                          ->groupBy('code');
                })
                ->get();
    }

    /**
     * Get all credential from DB for a position.
     * @param $credentialCode
     */
    public function getAllByPosition($credentialCode)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();
        return
            DB::table('position_credentials')
                ->select(
                    'position_credentials.id',
                    'position_credentials.position_code as positionCode',
                    'position_credentials.credential_code as credentialCode',
                    'position_credentials.eff_begin as effBegin',
                    'position_credentials.eff_end as effEnd',
                    'positions.code as positionCode',
                    'positions.name as positionName',
                    'jobs.name as jobName',
                    'units.name as unitName'
                )
                ->join('positions', function ($join) use($companyId, $tenantId, $now)  {
                    $join->on('positions.code', '=', 'position_credentials.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId],
                            ['positions.eff_begin', '<=', $now],
                            ['positions.eff_end', '>=', $now]
                        ]);
                })
                ->join('units',  function ($join) use($companyId, $tenantId)  {
                    $join->on('units.code', '=', 'positions.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->join('jobs',  function ($join) use($companyId, $tenantId)  {
                    $join->on('jobs.code', '=', 'positions.job_code')
                        ->where([
                            ['jobs.tenant_id', $tenantId],
                            ['jobs.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['position_credentials.tenant_id', $tenantId],
                    ['position_credentials.company_id', $companyId],
                    ['position_credentials.credential_code', $credentialCode],
                    ['position_credentials.eff_begin', '<=', $now],
                    ['position_credentials.eff_end', '>=', $now],
                ])
                ->get();
    }

    /**
     * Insert position credentials data into DB.
     * @param obj
     */
    public function save($obj)
    {
        DB::table('position_credentials')->insert($obj);
    }

    /**
     * Delete position credentials data from DB by id.
     * @param positionCode
     */
    public function delete($positionCode)
    {
        DB::table('position_credentials')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['position_code', $positionCode]
            ])
            ->delete();
    }
}
