<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanyDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all company in one tenant
     * @param  tenantId
     */
    public function getAll($tenantId)
    {
        return
            DB::table('companies')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'name',
                'description',
                'file_logo as fileLogo',
                'company_tax_number as companyTaxNumber',
                'tax_withholder_number as taxWithholderNumber',
                'tax_withholder_name as taxWithholderName',
                'location_code as locationCode',
                'sort_order as sortOrder'
            )
            ->where([
                ['tenant_id', $tenantId]
            ])
            ->get();
    }

    /**
     * Get one company
     * @param  tenantId , companyId
     */
    public function getOne($companyId)
    {
        return
            DB::table('companies')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'name',
                'description',
                'file_logo as fileLogo',
                'company_tax_number as companyTaxNumber',
                'tax_withholder_number as taxWithholderNumber',
                'tax_withholder_name as taxWithholderName',
                'location_code as locationCode',
                'sort_order as sortOrder'
            )
            ->where([
                ['companies.tenant_id', $this->requester->getTenantId()],
                ['id', $companyId]
            ])
            ->first();
    }

    /**
     * Get one company
     * @param  sortOrder
     */
    public function getOneBySortOrder($sortOrder)
    {
        return
            DB::table('companies')
            ->select(
                'id'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['sort_order', $sortOrder]
            ])
            ->first();
    }

    /**
     * Get one company
     * @param  tenantId , companyId
     */
    public function getSortOrder()
    {
        return
            DB::table('companies')
            ->select(
                'sort_order as sortOrder'
            )
            ->orderBy('sort_order', 'DESC')
            ->first();
    }

    /**
     * Get company id
     * @param  companyId
     */
    public function getCompanyById($companyId)
    {
        return
            DB::table('companies')
            ->select('name')
            ->where([
                ['id', $companyId]
            ])
            ->first();
    }

    /**
     * Get company name from many company id
     * @param int $tenantId
     * @param array|null $companyIds
     * @return
     */
    public function getCompanyByManyId(int $tenantId, array $companyIds)
    {
        $result = DB::table('companies')
            ->select('id', 'name')
            ->where([
                ['tenant_id', $tenantId],
            ])
            ->whereIn('id', $companyIds);

        return $result->get();
    }

    /**
     * Find setting for companies
     * @param int $tenantId
     * @param array|null $companyIds
     * @return
     */
    public function findSetting(int $tenantId, array $companyIds)
    {
        $result = DB::table('company_settings')
            ->select(
                'company_id as companyId',
                'companies.name as companyName',
                'setting_type_code as lovTypeCode',
                'setting_lov_key_data as lovKeyData',
                'fix_value as fixValue'
            )
            ->join('companies', function ($join) {
                $join->on('company_id', '=', 'companies.id');
            })
            ->where([
                ['companies.tenant_id', $tenantId],
            ])
            ->whereRaw('? between eff_begin and eff_end', [Carbon::today()]);

        if (!empty($companyIds)) {
            $result->whereIn('company_id', $companyIds);
        }

        return $result->get();
    }

    /**
     * Find setting for companies
     * @param int $tenantId
     * @param array|null $companyIds
     * @return
     */
    public function getSetting(int $tenantId, int $companyId)
    {
        $result = DB::table('company_settings')
            ->select(
                'setting_type_code as lovTypeCode',
                'setting_lov_key_data as lovKeyData',
                'fix_value as fixValue'
            )
            ->join('companies', function ($join) {
                $join->on('company_id', '=', 'companies.id');
            })
            ->join('setting_types', function ($join) {
                $join->on('code', '=', 'company_settings.setting_type_code');
            })
            ->where([
                ['company_settings.tenant_id', $tenantId],
                ['company_settings.company_id', $companyId]
            ])
            ->whereRaw('? between eff_begin and eff_end', [Carbon::today()]);

        return $result->get();
    }

    public function getAllCompanySettings(int $companyId)
    {
        return DB::table('setting_types')
            ->select(
                'setting_types.code as typeCode',
                'setting_types.name as typeName',
                'setting_lov_key_data as lovKeyData',
                'company_settings.fix_value as fixValue',
                'setting_types.vtype'
            )
            ->leftJoin('company_settings', 'setting_type_code', 'setting_types.code')
            ->where([
                ['company_settings.tenant_id', $this->requester->getTenantId()],
                ['company_settings.company_id', $companyId]
            ])
            ->orWhere(function ($query) {
                $query->whereNull('company_settings.tenant_id')
                    ->whereNull('company_settings.company_id');
            })
            ->get();
    }

    /**
     * Insert data company to DB
     * @param  array obj
     */
    public function saveCompanySetting($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        DB::table('company_settings')->insert($obj);
    }


    /**
     * Insert data company to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'companies', $obj);

        DB::table('companies')->insert($obj);
    }

    /**
     * Update data company to DB
     * @param  array tenantId, companyId, obj
     */
    public function update($companyId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'companies', $obj);

        DB::table('companies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $companyId]
            ])
            ->update($obj);
    }

    /**
     * Update data company to DB
     * @param  array tenantId, companyId, obj
     */
    public function updateCompanySetting($companyId, $settingTypeCode, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('company_settings')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['setting_type_code', $settingTypeCode]
            ])
            ->update($obj);
    }

    public function deleteCompanySettings($companyId)
    {
        DB::table('company_settings')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
            ])
            ->delete();
    }
}
