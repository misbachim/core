<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonDocumentDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all PersonDocuments
     * @param personId
     */
    public function getAll($personId)
    {
        return
            DB::table('person_documents')
                ->select(
                    'person_documents.id',
                    'person_documents.name',
                    'expired',
                    'file_document as fileDocument',
                    'dcty.val_data as dcty',
                    'lov_dcty as lovDcty'
                )
                ->leftJoin('lovs as dcty', function ($join) {
                    $join->on('dcty.key_data', '=', 'person_documents.lov_dcty')
                        ->where([
                            ['dcty.lov_type_code', 'DCTY'],
                            ['dcty.tenant_id', $this->requester->getTenantId()],
                            ['dcty.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['person_documents.tenant_id', $this->requester->getTenantId()],
                    ['person_documents.person_id', $personId]
                ])
                ->get();
    }

//    /**
//     * Get all Expired PersonDocuments
//     * @param $months
//     */
//    public function getAllByExpired()
//    {
//        $now = Carbon::now();
//
//        return
//            DB::table('person_documents')
//                ->select(
//                    'person_documents.id',
//                    'person_documents.name',
//                    'expired',
//                    'file_document as fileDocument',
//                    'dcty.val_data as dcty',
//                    'lov_dcty as lovDcty'
//                )
//                ->join('lovs as dcty', function ($join) {
//                    $join->on('dcty.key_data', '=', 'person_documents.lov_dcty')
//                        ->where([
//                            ['dcty.lov_type_code', 'DCTY'],
//                            ['dcty.arg1', 'T'],
//                            ['dcty.tenant_id', $this->requester->getTenantId()],
//                            ['dcty.company_id', $this->requester->getCompanyId()]
//                        ]);
//                })
//                ->where([
//                    ['person_documents.tenant_id', $this->requester->getTenantId()],
//                    ['person_documents.expired', '<', $now]
//                ])
//                ->get();
//    }
//
//    /**
//     * Get all Expired PersonDocuments
//     * @param $months
//     */
//    public function getAllByMonth($months)
//    {
//        $now = Carbon::now();
//        $max = Carbon::parse($now)->addMonths($months);
//
//        return
//            DB::table('person_documents')
//                ->select(
//                    'person_documents.id',
//                    'person_documents.name',
//                    'expired',
//                    'file_document as fileDocument',
//                    'dcty.val_data as dcty',
//                    'lov_dcty as lovDcty'
//                )
//                ->join('lovs as dcty', function ($join) {
//                    $join->on('dcty.key_data', '=', 'person_documents.lov_dcty')
//                        ->where([
//                            ['dcty.lov_type_code', 'DCTY'],
//                            ['dcty.arg1', 'T'],
//                            ['dcty.tenant_id', $this->requester->getTenantId()],
//                            ['dcty.company_id', $this->requester->getCompanyId()]
//                        ]);
//                })
//                ->where([
//                    ['person_documents.tenant_id', $this->requester->getTenantId()],
//                    ['person_documents.expired', '>=', $now],
//                    ['person_documents.expired', '<=', $max]
//                ])
//                ->get();
//
//    }

    /**
     * Get all Expired dan Nearly Expired PersonDocuments
     * @param $months
     */
    public function getAllByFlagAndExpiredInThreeMonths()
    {
        $now = Carbon::now();
        $max = Carbon::parse($now)->addMonths(3);
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_documents')
                ->select(
                    'person_documents.id',
                    'person_documents.name',
                    'expired',
                    'file_document as fileDocument',
                    'dcty.val_data as dcty',
                    'lov_dcty as lovDcty'
                )
                ->selectRaw('CONCAT(persons.first_name,\' \',persons.last_name) as "fullName"')
                ->leftJoin('persons', function ($join) use ($tenantId, $now) {
                    $join->on('person_documents.person_id', '=', 'persons.id')
                        ->where([
                            ['persons.eff_begin', '<=', $now],
                            ['persons.eff_end', '>=', $now],
                            ['persons.tenant_id', $tenantId]
                        ]);
                })
                ->join('lovs as dcty', function ($join) use ($companyId, $tenantId) {
                    $join->on('dcty.key_data', '=', 'person_documents.lov_dcty')
                        ->where([
                            ['dcty.lov_type_code', 'DCTY'],
                            ['dcty.arg1', 'T'],
                            ['dcty.tenant_id', $tenantId],
                            ['dcty.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_documents.tenant_id', $this->requester->getTenantId()],
                    ['person_documents.expired', '<=', $max]
                ])
                ->get();

    }

    /**
     * Get personDocument based on person id and personDocument id
     * @param $personId
     * @param $personDocumentId
     * @return
     */
    public function getOne($personId, $personDocumentId)
    {
        return
            DB::table('person_documents')
                ->select(
                    'person_documents.id',
                    'person_documents.name',
                    'lov_dcty as lovDcty',
                    'expired',
                    'file_document as fileDocument',
                    'dcty.val_data as dcty'
                )
                ->leftJoin('lovs as dcty', function ($join) {
                    $join->on('dcty.key_data', '=', 'person_documents.lov_dcty')
                        ->where([
                            ['dcty.lov_type_code', 'DCTY'],
                            ['dcty.tenant_id', $this->requester->getTenantId()],
                            ['dcty.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['person_documents.tenant_id', $this->requester->getTenantId()],
                    ['person_documents.person_id', $personId],
                    ['person_documents.id', $personDocumentId]
                ])
                ->first();
    }

    /**
     * Insert data PersonDocument to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_documents')->insertGetId($obj);
    }

    /**
     * Update data PersonDocument to DB
     * @param $personId
     * @param $personDocumentId
     * @param $obj
     */
    public function update($personId, $personDocumentId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_documents')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId],
                ['id', $personDocumentId]
            ])
            ->update($obj);
    }

    /**
     * Delete data PersonDocument from DB
     * @param  personDocumentId
     */
    public function delete($personDocumentId)
    {
        DB::table('person_documents')->where('id', $personDocumentId)->delete();
    }
}
