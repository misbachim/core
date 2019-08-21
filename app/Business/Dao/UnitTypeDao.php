<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitTypeDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all unit type in ONE company
     * @param
     */
    public function getAll()
    {
        return
            DB::table('unit_types')
                ->select(
                    'code',
                    'name',
                    'unit_level as unitLevel'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get all unitType in ONE company
     * @param  $unitTypeCode
     */
    public function getLov()
    {
        return
            DB::table('unit_types')
                ->select(
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get one unit type based on unit type code
     * @param code
     */
    public function getOne($code)
    {
        return
            DB::table('unit_types')
                ->select(
                    'code',
                    'name',
                    'unit_level as unitLevel'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $code]
                ])
                ->first();
    }

    /**
     * Insert data unit type to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'unit_types', $obj);

        DB::table('unit_types')->insert($obj);
    }

    /**
     * Update data unit type to DB
     * @param code, array obj
     */
    public function update( $code, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'unit_types', $obj);

        DB::table('unit_types')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['code', $code]
        ])
        ->update($obj);
    }

    /**
     * Delete data unit type from DB
     * @param  countryId
     */
    public function delete($code)
    {
        DB::table('unit_types')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->delete();
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateUnitTypeCode(string $code)
    {
        return DB::table('unit_types')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }


}
