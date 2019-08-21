<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestSocmedsDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }



    /**
     * Insert data Person Families Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('request_socmeds')->insertGetId($obj);
    }



}
