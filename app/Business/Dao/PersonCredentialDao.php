<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonCredentialDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person_credentials
     * @param  offset, limit
     */
    public function getAll($offset, $limit, $personId)
    {
        return
            DB::table('person_credentials')
                ->select(
                    'id',
                    'credential_code as credentialCode',
                    'no_credential as credentialNumber',
                    'begin_date as beginDate',
                    'end_date as endDate',
                    'document as document'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['person_id',  $personId],
                    ['end_date', '>=', Carbon::now()],
                    ['flag_delete',  0]
                ])
                ->orderByRaw('end_date DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all person_credentials
     * @param  offset, limit
     */
    public function getAllInactive($offset, $limit, $personId)
    {
        return
            DB::table('person_credentials')
                ->select(
                    'id',
                    'credential_code as credentialCode',
                    'no_credential as credentialNumber',
                    'begin_date as beginDate',
                    'end_date as endDate',
                    'document as document'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['person_id',  $personId],
                    ['end_date', '<=', Carbon::now()],
                    ['flag_delete',  0]
                ])
                ->orderByRaw('end_date DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get one person_credentials based on person_credentials code
     * @param  ratingScaleId
     */
    public function getOne($personCredentialId)
    {
        return
            DB::table('person_credentials')
                ->select(
                    'id',
                    'credential_code as credentialCode',
                    'no_credential as credentialNumber',
                    'begin_date as beginDate',
                    'end_date as endDate',
                    'document as document'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['flag_delete',  0],
                    ['id', $personCredentialId]
                ])
                ->orderBy('end_date', 'DESC')
                ->first();
    }

    /**
     * Get total data
     * @param
     */
    public function getTotalRow()
    {
        return DB::table('person_credentials')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
     * Insert data person_credentials to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_credentials')->insertGetId($obj);
    }

    /**
     * Update data person_credentials to DB
     * @param  array obj, id
     */
    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_credentials')
        ->where([
            ['id', $id]
        ])
        ->update($obj);
    }

    /**
     * delete (set flag_delete = 1) on person_credentials based on id
     * @param  array id
     */
    public function delete($id)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_credentials')
        ->where([
            ['id', $id]
        ])
        ->update(['flag_delete' => 1]);
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateRatingScaleCode(string $code)
    {
        return DB::table('person_credentials')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }
}
