<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CountryDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all country
     * @param  offset, limit
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('countries')
                ->select(
                    'id',
                    'code',
                    'name',
                    'dial_code as dialCode',
                    'nationality'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all countries (lib+countries) in ONE company
     * @param
     */
    public function getLov()
    {
        return
            DB::table('countries')
                ->select(
                    'id',
                    'code',
                    'name',
                    'nationality'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('countries')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
     * Get one country based on country id
     * @param  countryId
     */
    public function getOne($countryId)
    {
        return
            DB::table('countries')
                ->select(
                    'id',
                    'code',
                    'name',
                    'dial_code as dialCode',
                    'nationality'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $countryId],
                ])
                ->first();
    }

    /**
     * Insert data country to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'countries', $obj);

        return DB::table('countries')->insertGetId($obj);
    }

    /**
     * Update data country to DB
     * @param  array obj, countryId
     */
    public function update($countryId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'countries', $obj);

        DB::table('countries')
        ->where([
            ['id', $countryId]
        ])
        ->update($obj);
    }

    /**
     * Delete data country from DB
     * @param  countryId
     */
    public function delete($countryId)
    {
        DB::table('countries')->where('id', $countryId)->delete();
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateCountryCode(string $code)
    {
        return DB::table('countries')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }


    /**
     * @param string $code
     * @param $id if update data, then check duplicate code beside current user id
     * @return
     */
    public function checkDuplicateEditCountryCode(string $code,$id)
    {
        $result = DB::table('countries')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($id)) {
            $result->where('id', '!=', $id);
        }

        return $result->count();
    }

    /**
     * Check usage. If result > 0 it means there is a dependency.
     * @param int $countryId
     * @return mixed
     */
    public function getTotalUsage(int $countryId)
    {
        return DB::table('countries')
            ->join('provinces', function ($join) {
                $join
                    ->on('countries.code', '=', 'provinces.country_code');
            })
            ->where('countries.id', $countryId)
            ->where('countries.tenant_id', $this->requester->getTenantId())
            ->count();
    }

}
