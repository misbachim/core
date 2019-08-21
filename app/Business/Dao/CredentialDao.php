<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CredentialDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Credential Group By Code
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('credentials')
                ->select(
                    'code',
                    'name',
                    'description',

                    'renewal_cycle as renewalCycle',
                    'notification_days as notificationDays',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->whereIn('credentials.id', function ($query) {
                    $query->select(DB::raw('MAX(credentials.id) as id'))
                            ->from('credentials')
                            ->groupBy('credentials.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Active Credential Group By Code
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAllActive($offset,$limit)
    {
        return
            DB::table('credentials')
                ->select(
                    'code',
                    'name',
                    'description',

                    'renewal_cycle as renewalCycle',
                    'notification_days as notificationDays',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->whereIn('credentials.id', function ($query) {
                    $query->select(DB::raw('MAX(credentials.id) as id'))
                            ->from('credentials')
                            ->groupBy('credentials.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Active Credential Group By Code
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAllInactive($offset,$limit)
    {
        return
            DB::table('credentials')
                ->select(
                    'code',
                    'name',
                    'description',
                    'renewal_cycle as renewalCycle',
                    'notification_days as notificationDays',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_end', '<', Carbon::now()]
                ])
                ->whereIn('credentials.id', function ($query) {
                    $query->select(DB::raw('MAX(credentials.id) as id'))
                            ->from('credentials')
                            ->groupBy('credentials.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get LOV all Credential in ONE company
     * @param effBegin, effEnd
     */
    public function getLov()
    {
        return
            DB::table('credentials')
                ->select(
                    'code',
                    'name',
                    'renewal_cycle as renewalCycle'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->whereIn('credentials.id', function ($query) {
                    $query->select(DB::raw('MAX(credentials.id) as id'))
                            ->from('credentials')
                            ->groupBy('credentials.code')
                            ->get();
                })
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get one credentials based on credentials code
     * @param  $credentialCode
     */
    public function getOne($credentialCode)
    {
        return
            DB::table('credentials')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'renewal_cycle as renewalCycle',
                    'notification_days as notificationDays',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $credentialCode],
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }

    /**
     * Get all credentials based on credentials code
     * @param  $credentialCode, $credentialId
     */
    public function getAllByCode($credentialCode, $credentialId)
    {
        return
            DB::table('credentials')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'renewal_cycle as renewalCycle',
                    'notification_days as notificationDays',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $credentialCode],
                    ['id', '!=', $credentialId],
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    /**
     * Get all credentials based on Qualification Source
     * @param  $qsName
     */
    public function getAllByQualificationSource($qsName)
    {
        return
            DB::table('credentials')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'renewal_cycle as renewalCycle',
                    'notification_days as notificationDays',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['qualification_source_name', $qsName]
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    /**
     * Insert data credentials to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'credentials', $obj);

        return DB::table('credentials')->insertGetId($obj);
    }

    /**
     * Update data credential to DB
     * @param  array obj, credentialId
     */
    public function update($credentialId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'credentials', $obj);

        DB::table('credentials')
            ->where([
                ['id', $credentialId]
            ])
            ->update($obj);
    }

    /**
     * Check Duplicate Credential Code in data credentials
     * @param string $code
     * @return
     */
    public function checkDuplicateCredentialCode(string $code)
    {
        return DB::table('credentials')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

}
