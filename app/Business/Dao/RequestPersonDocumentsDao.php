<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestPersonDocumentsDao
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
        return
            DB::table('request_person_documents')
                ->select(
                    'request_person_documents.profile_request_id as profileRequestId',
                    'request_person_documents.id',
                    'request_person_documents.crud_type as crudType',
                    'request_person_documents.person_document_id as personDocumentId',
                    'request_person_documents.lov_dcty as lovDcty',
                    'dcty.val_data as dcty',
                    'request_person_documents.file_document as file_document',
                    'request_person_documents.name',
                    'request_person_documents.expired'
                )
                ->leftJoin('lovs as dcty', function ($join) {
                    $join->on('dcty.key_data', '=', 'request_person_documents.lov_dcty')
                        ->where([
                            ['dcty.lov_type_code', 'DCTY'],
                            ['dcty.tenant_id', $this->requester->getTenantId()],
                            ['dcty.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['request_person_documents.tenant_id', $this->requester->getTenantId()],
                    ['request_person_documents.profile_request_id', $profileRequestId]
                ])
                ->get();
    }

    public function getOne($personDocumentId)
    {
        return
            DB::table('request_person_documents')
                ->select(
                    'id',
                    'crud_type as crudType',
                    'profile_request_id as profileRequestId',
                    'person_document_id as personDocumentId',
                    'lov_dcty as lovDcty',
                    'file_document as fileDocument',
                    'name',
                    'expired'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['id', $personDocumentId]
                ])
                ->first();
    }

    /**
     * Insert data Person Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        return DB::table('request_person_documents')->insertGetId($obj);
    }

    public function update($personDocumentId, $obj)
    {
        DB::table('request_person_documents')
            ->where([
                ['id', $personDocumentId]
            ])
            ->update($obj);
    }

    public function delete($personDocumentId)
    {
        DB::table('request_person_documents')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personDocumentId]
            ])
            ->delete();
    }
}
