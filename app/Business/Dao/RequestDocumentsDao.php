<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestDocumentsDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all RequestedPersonDocuments
     * @param personId
     */
    public function getAll($personId)
    {
        return
            DB::table('request_documents')
                ->select(
                    'request_documents.company_id as companyId',
                    'request_documents.id',
                    'crud_type as crudType',
                    'request_documents.person_id as personId',
                    'request_documents.employee_id as employeeId',
                    'person_document_id as personDocumentId',
                    'request_documents.lov_dcty as lovDcty',
                    'dcty.val_data as dcty',
                    'file_document as file_document',
                    'request_documents.name',
                    'expired',
                    'status',
                    'request_date as requestDate'
                )->leftJoin('lovs as dcty',  function ($join)  {
                    $join->on('dcty.key_data', '=', 'request_documents.lov_dcty')
                        ->where([
                            ['dcty.lov_type_code', 'DCTY'],
                            ['dcty.tenant_id', $this->requester->getTenantId()],
                            ['dcty.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['request_documents.tenant_id', $this->requester->getTenantId()],
                    ['request_documents.person_id', $personId]
                ])
                ->get();
    }

    public function checkIfRequestIsPending($employeeId, $status){
        return
            DB::table('request_documents')
                ->select(
                    'id',
                    'employee_id',
                    'person_id',
                    'person_document_id as personDocumentId',
                    'crud_type as crudType',
                    'status'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['employee_id', $employeeId],
                    ['status', $status]
                ])
                ->get();
    }

    public function getOne($personAddressId)
    {
        return
            DB::table('request_documents')
                ->select(
                    'request_documents.company_id as companyId',
                    'request_documents.tenant_id as tenantId',
                    'request_documents.id',
                    'crud_type as crudType',
                    'request_documents.person_id as personId',
                    'request_documents.employee_id as employeeId',
                    'person_document_id as personDocumentId',
                    'request_documents.lov_dcty as lovDcty',
                    'dcty.val_data as dcty',
                    'file_document as fileDocument',
                    'request_documents.name',
                    'expired',
                    'status',
                    'request_date as requestDate'
                )
                ->leftJoin('lovs as dcty',  function ($join)  {
                    $join->on('dcty.key_data', '=', 'request_documents.lov_dcty')
                        ->where([
                            ['dcty.lov_type_code', 'DCTY'],
                            ['dcty.tenant_id', $this->requester->getTenantId()],
                            ['dcty.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['request_documents.tenant_id', $this->requester->getTenantId()],
                    ['request_documents.id', $personAddressId]
                ])
                ->first();
    }

    /**
     * Insert data Person Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('request_documents')->insertGetId($obj);
    }

    public function update($personAddressId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('request_documents')
            ->where([
                ['id', $personAddressId]
            ])
            ->update($obj);
    }

    public function delete($personAddressId)
    {
        DB::table('request_documents')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personAddressId]
            ])
            ->delete();
    }
}
