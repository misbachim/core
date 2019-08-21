<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestPersonSocmedsDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }


    /**
     * @param $profileRequestId
     */
    public function getMany($profileRequestId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('request_person_socmeds')
                ->selectRaw(
                    'request_person_socmeds.profile_request_id as profileRequestId,'.
                    'request_person_socmeds.id',
                    'request_person_socmeds.lov_socm as lovSocm',
                    'social_medias.val_data as socialMedia',
                    'request_person_socmeds.account'
                )
                ->leftJoin('lovs as social_medias', function ($join) use($companyId, $tenantId)  {
                    $join->on('social_medias.key_data', '=', 'request_person_socmeds.lov_socm')
                        ->where([
                            ['social_medias.lov_type_code', 'SOCM'],
                            ['social_medias.tenant_id', $tenantId],
                            ['social_medias.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['request_person_socmeds.tenant_id', $tenantId],
                    ['request_person_socmeds.profile_request_id', $profileRequestId]
                ])
                ->get();
    }

    /**
     * Get one request person socmed based on $personSocmedId
     * @param $personId
     * @param $personFamilyId
     * @return
     */
    public function getOne($personSocmedId)
    {
        return
            DB::table('request_person_socmeds')
                ->select(
                    'id',
                    'profile_request_id as profileRequestId',
                    'person_socmed_id as personSocmedId',
                    'crud_type as crudType',
                    'lov_socm as lovSocm',
                    'account'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['id', $personSocmedId]
                ])
                ->first();
    }

    /**
     * Insert data Person Socmed Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        return DB::table('request_person_socmeds')->insertGetId($obj);
    }



}
