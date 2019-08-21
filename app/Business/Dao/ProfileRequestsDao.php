<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfileRequestsDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * @param $getAll
     */
    public function getAll()
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('profile_requests')
                ->selectRaw(
                    'id',
                    'status',
                    'person_id as personId'
                )
                ->where([
                    ['tenant_id', $tenantId],
                ])
                ->get();
    }

    public function getOne($profileRequestId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('profile_requests')
                ->selectRaw(
                    'id',
                    'status',
                    'person_id as personId'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['id', $profileRequestId]
                ])
                ->first();

    }

    public function checkIfRequestIsPending($personId, $status)
    {
        return
            DB::table('profile_requests')
                ->select(
                    'id',
                    'person_id as personId',
                    'status'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['status', $status]
                ])
                ->get();
    }

    /**
     * Insert data Profile Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('profile_requests')->insertGetId($obj);
    }

    /**
     * Update data Profile Request to DB
     * @param $personId
     * @param $obj
     */
    public function update($personId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('profile_requests')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personId]
            ])
            ->update($obj);
    }


}
