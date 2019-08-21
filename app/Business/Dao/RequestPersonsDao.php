<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestPersonsDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * @param $profileRequestId
     */
    public function getOneByProfileRequestId($profileRequestId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('request_persons')
                ->selectRaw(
                    'request_persons.profile_request_id as profileRequestId,'.
                    'request_persons.id',
                    'request_persons.lov_mars as lovMars',
                    'request_persons.lov_gndr as lovGndr',
                    'request_persons.lov_rlgn as lovRlgn',
                    'request_persons.lov_blod as lovBlod',
                    'request_persons.country_code as countryCode',
                    'request_persons.id_card as idCard',
                    'request_persons.first_name as firstName',
                    'request_persons.last_name as lastName',
                    'request_persons.birth_place as birthPlace',
                    'request_persons.birth_date as birthDate',
                    'request_persons.email',
                    'request_persons.phone',
                    'request_persons.mobile',
                    'request_persons.hobbies',
                    'request_persons.weakness',
                    'request_persons.strength',
                    'marital_statuses.val_data as maritalStatus',
                    'genders.val_data as gender',
                    'request_persons.file_photo as filePhoto'
                )
                ->leftJoin('lovs as marital_statuses', function ($join) use($companyId, $tenantId)  {
                    $join->on('marital_statuses.key_data', '=', 'request_persons.lov_socm')
                        ->where([
                            ['marital_statuses.lov_type_code', 'MARS'],
                            ['marital_statuses.tenant_id', $tenantId],
                            ['marital_statuses.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as genders', function ($join) use($companyId, $tenantId)  {
                    $join->on('marital_statuses.key_data', '=', 'request_persons.lov_gndr')
                        ->where([
                            ['marital_statuses.lov_type_code', 'GNDR'],
                            ['marital_statuses.tenant_id', $tenantId],
                            ['marital_statuses.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['request_persons.tenant_id', $tenantId],
                    ['request_persons.profile_request_id', $profileRequestId]
                ])
                ->first();
    }

    /**
     * Insert data Person Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        return DB::table('request_persons')->insertGetId($obj);
    }

    /**
     * Update data Person Request to DB
     * @param $personId
     * @param $obj
     */
    public function update($personId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('request_persons')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personId]
            ])
            ->update($obj);
    }



}
