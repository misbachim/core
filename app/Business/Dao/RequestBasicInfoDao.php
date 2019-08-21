<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestBasicInfoDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Insert data Person Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('request_basic_info')->insertGetId($obj);
    }

    /**
     * Update data Person to DB
     * @param $personId
     * @param $obj
     */
    public function update($personId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('persons')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personId]
            ])
            ->update($obj);
    }



}
