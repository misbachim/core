<?php

namespace App\Business\Dao;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;
//use Illuminate\Database\Schema;
use Illuminate\Support\Facades\Schema;
use stdClass;

class MigrationToolDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all migration tools module
     */
    public function getAllMtModule()
    {
        return
            DB::table('mt_modules')
                ->select('id', 'code', 'name')
                ->where('is_migration', true)
                ->get();
    }

    public function getTemp($code)
    {
        return
            DB::table('mt_temps')
                ->select('id')
                ->where([
                    ['company_id', $this->requester->getCompanyId()],
                    ['tenant_id', $this->requester->getTenantId()],
                    ['mt_module_code', $code]
                ])
                ->orderBy('id', 'asc')
                ->get();
    }

    public function getAllTempField($code)
    {
        return
            DB::table('mt_temps')
                ->where([
                    ['company_id', $this->requester->getCompanyId()],
                    ['tenant_id', $this->requester->getTenantId()],
                    ['mt_module_code', $code]
                ])
                ->get();
    }

    public function getAllTemp($code, $columns)
    {
        $conversion = implode(',', $columns);
        $join = '' . $conversion . '';
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $query = "SELECT id, $join FROM mt_temps WHERE mt_module_code = '$code' AND company_id = '$companyId'
                  AND tenant_id = '$tenantId'
                  ORDER BY id ASC";
        return
            DB::select($query);
    }

    public function getFieldNameTempSchema($code)
    {
        $query = "SELECT column_name as columnName, mt_attributes.temp_field_name as tempFieldName,
                  mt_attributes.data_type as dataType, mt_attributes.dest_service as destService,
                  mt_attributes.dest_field as destField, mt_attributes.dest_table as destTable
                  FROM INFORMATION_SCHEMA.COLUMNS
                  join mt_attributes ON mt_attributes.temp_field_name = INFORMATION_SCHEMA.COLUMNS.column_name
                  WHERE TABLE_NAME = N'mt_temps' AND mt_attributes.mt_module_code = '$code'";
        return
            DB::select($query);
    }

    public function getFieldNameTempSchemaNullValue($code)
    {
        $query = "SELECT column_name as columnName, mt_attributes.temp_field_name as tempFieldName,
                  mt_attributes.data_type as dataType,
                  mt_attributes.dest_field as destField, mt_attributes.dest_table as destTable
                  FROM INFORMATION_SCHEMA.COLUMNS
                  join mt_attributes ON mt_attributes.temp_field_name = INFORMATION_SCHEMA.COLUMNS.column_name
                  WHERE TABLE_NAME = N'mt_temps' AND mt_attributes.mt_module_code = '$code'AND mt_attributes.dest_table IS NULL";
        return
            DB::select($query);
    }

    public function getOneModule($code)
    {
        return
            DB::table('mt_modules')
                ->select(
                    'id',
                    'code',
                    'name',
                    'custom',
                    'is_company as isCompany'
                )
                ->where('code', $code)
                ->first();
    }

    public function getMtAttributes($mtModuleCode, $destTable)
    {
        return
            DB::table('mt_attributes')
                ->select(
                    'id',
                    'name',
                    'temp_field_name as tempFieldName',
                    'data_type as dataType',
                    'default_value as defaultValue',
                    'dest_table as destTable',
                    'dest_field as destField'
                )
                ->where([
                    ['mt_module_code', $mtModuleCode],
                    ['dest_table', $destTable]
                ])
                ->get();
    }

    public function getMtAttributesWithoutDestTable($mtModuleCode)
    {
        return
            DB::table('mt_attributes')
                ->select(
                    'id',
                    'name',
                    'temp_field_name as tempFieldName',
                    'data_type as dataType',
                    'is_mandatory as isMandatory',
                    'default_value as defaultValue',
                    'default_value_type as defaultValueType',
                    'min',
                    'max',
                    'decimal',
                    'regex',
                    'is_lookup as isLookup',
                    'lookup_service as lookupService',
                    'lookup_table as lookupTable',
                    'lookup_field as lookupField',
                    'lookup_condition as lookupCondition',
                    'dest_table as destTable',
                    'dest_field as destField'
                )
                ->where([
                    ['mt_module_code', $mtModuleCode]
                ])
                ->get();
    }

    public function lovModuleAttributes($code)
    {
        return
            DB::table('mt_attributes')
                ->select(
                    'id',
                    'name',
                    'temp_field_name as tempFieldName',
                    'regex'
                )
                ->where('mt_module_code', $code)
                ->whereNotNull('temp_field_name')
                ->orderBy('temp_field_name', 'asc')
                ->get();
    }

    public function getModuleAttributes($code)
    {
        return
            DB::table('mt_attributes')
                ->select(
                    'id',
                    'name',
                    'temp_field_name as tempFieldName',
                    'data_type as dataType',
                    'is_mandatory as isMandatory',
                    'default_value as defaultValue',
                    'min',
                    'max',
                    'decimal',
                    'regex',
                    'is_lookup as isLookup',
                    'lookup_service as lookupService',
                    'lookup_table as lookupTable',
                    'lookup_field as lookupField'
                )
                ->where([
                    ['mt_module_code', $code],
                    ['is_hidden', false]
                ])
                ->orderBy('id', 'asc')
                ->get();
    }

    public function getModuleAttributesWithoutNullFieldName($code)
    {
        return
            DB::table('mt_attributes')
                ->select(
                    'id',
                    'name',
                    'temp_field_name as tempFieldName',
                    'data_type as dataType',
                    'is_mandatory as isMandatory',
                    'default_value as defaultValue',
                    'min',
                    'max',
                    'decimal',
                    'regex',
                    'is_lookup as isLookup',
                    'lookup_service as lookupService',
                    'lookup_table as lookupTable',
                    'lookup_field as lookupField',
                    'lookup_condition as lookupCondition'
                )
                ->where('mt_module_code', $code)
                ->whereNotNull('temp_field_name')
                ->orderBy('id', 'asc')
                ->get();
    }

    public function getDataFromTable($lookupService, $lookupTable, $lookupField, $lookupCondition, $fieldValue)
    {
        if($lookupService === 'core') {
            return
                DB::table($lookupTable)
                    ->select($lookupCondition)
                    ->where([
                        ['company_id', $this->requester->getCompanyId()],
                        ['tenant_id', $this->requester->getTenantId()],
                        [$lookupField, $fieldValue]
                    ])
                    ->first();
        } else {
            return
                DB::connection($lookupService)
                    ->table($lookupTable)
                    ->select($lookupCondition)
                    ->where([
                        ['company_id', $this->requester->getCompanyId()],
                        ['tenant_id', $this->requester->getTenantId()],
                        [$lookupField, $fieldValue]
                    ])
                    ->first();
        }
    }

    public function getFieldNameWithRegex($code, $id, $columns)
    {
        $conversion = implode(',', $columns);
        return
            DB::table('mt_temps')
                ->select(
                    'mt_attributes.temp_field_name as tempFieldName',
                    'mt_attributes.name as fieldName',
                    'mt_attributes.data_type as dataType',
                    'mt_attributes.dest_field as destField',
                    'mt_attributes.dest_table as destTable',
                    'mt_attributes.regex'
                )
                ->selectRaw($conversion)
                ->join('mt_attributes', 'mt_attributes.mt_module_code', 'mt_temps.mt_module_code')
                ->where([
                    ['mt_temps.mt_module_code', $code],
                    ['mt_temps.id', $id]
                ])
                ->whereNotNull('mt_attributes.temp_field_name')
                ->orderBy('mt_attributes.temp_field_name', 'asc')
                ->get();
    }

    public function saveTempRecords($tempRecords)
    {
        return DB::table('mt_temps')->insert($tempRecords);
    }

    public function saveActRecords($destTable, $destService, $tempRecords)
    {
        if($destService === 'core') {
            return DB::table($destTable)->insert($tempRecords);
        } else {
            return DB::connection($destService)
                ->table($destTable)->insert($tempRecords);
        }
    }

    public function saveActRecordsManyTable($destTable, $tempRecords)
    {
        return DB::table($destTable)->insertGetId($tempRecords);
    }

    public function saveDetailActRecordsManyTable($destTable, $tempRecords)
    {
        return DB::table($destTable)->insertGetId($tempRecords);
    }

    /**
     * Update data country to DB
     * @param  array obj, countryId
     */
    public function updateMtTemps($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('mt_temps')
            ->where([
                ['id', $id]
            ])
            ->update($obj);
    }

    /**
     * Update data mt temporary to DB
     * @param  array obj, tempId
     */
    public function updateAttachment($column, $fileName, $tempRecords)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('mt_temps')
            ->where([
                [$column, $fileName]
            ])
            ->update($tempRecords);
    }

    /**
     * Delete data mt temporary from DB
     * @param code
     */
    public function deleteAllTempRecord($code)
    {
        DB::table('mt_temps')->where([
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()],
            ['mt_module_code', $code]
        ])->delete();
    }

    /**
     * Delete data mt temporary from DB
     * @param code
     */
    public function deleteAllTempRecordWithUserId($code)
    {
        DB::table('mt_temps')->where([
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()],
            ['created_by', $this->requester->getUserId()],
            ['mt_module_code', $code]
        ])->delete();
    }

    public function search($query, $module)
    {
        $searchString = strtolower("%$query%");
        return
            DB::table('mt_batchs')
                ->select('id', 'batch_name as name')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['mt_module_code', $module]
                ])
                ->whereRaw('LOWER(batch_name) like ?', [$searchString])
                ->get();
    }

}
