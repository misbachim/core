<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlobalLovLearning2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $datas = DB::table('lovs')->select(
            'tenant_id as tenantId',
            'company_id as companyId'
        )
            ->distinct()
            ->get();

        foreach ($datas as $data) {
            DB::table('lovs')->insert([
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'WFA',
                    'val_data' => 'Waiting for Approval',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'RJE',
                    'val_data' => 'Rejected',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'NRE',
                    'val_data' => 'Need Registration',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'WLT',
                    'val_data' => 'Wait List',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'CON',
                    'val_data' => 'Confirmed',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'FTO',
                    'val_data' => 'Failed to Complete',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'CPL',
                    'val_data' => 'Completed',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'CCL',
                    'val_data' => 'Cancelled',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'EMDEVST',
                    'key_data' => 'WDN',
                    'val_data' => 'Withdrawn',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ASDEVSE',
                    'key_data' => 'CHOOSE',
                    'val_data' => 'Choose a session',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ASDEVSE',
                    'key_data' => 'FIRST',
                    'val_data' => 'First Available Session',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ASDEVSE',
                    'key_data' => 'ALLOW',
                    'val_data' => 'Allow Employee to Select Session',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ABSENCE',
                    'key_data' => '-',
                    'val_data' => '-',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ABSENCE',
                    'key_data' => 'P',
                    'val_data' => 'P',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ABSENCE',
                    'key_data' => 'HAE',
                    'val_data' => 'HA (E)',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ABSENCE',
                    'key_data' => 'HAUE',
                    'val_data' => 'HA (UE)',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ABSENCE',
                    'key_data' => 'AE',
                    'val_data' => 'A (E)',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
                [
                    'tenant_id' => $data->tenantId,
                    'company_id' => $data->companyId,
                    'lov_type_code' => 'ABSENCE',
                    'key_data' => 'AUE',
                    'val_data' => 'A (UE)',
                    'is_disableable' => false,
                    'is_active' => true,
                    'created_by' => 0,
                    'created_at' => '2018-01-01 00:00:00+00',
                    'updated_by' => null,
                    'updated_at' => null,
                    'arg1' => null,
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $datas = DB::table('lovs')->select(
            'tenant_id as tenantId',
            'company_id as companyId'
        )
            ->distinct()
            ->get();

        foreach ($datas as $data) {
            DB::table('lovs')->where('lov_type_code', '=', 'EMDEVST')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'ASDEVSE')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'ABSENCE')->delete();
        }
    }
}
