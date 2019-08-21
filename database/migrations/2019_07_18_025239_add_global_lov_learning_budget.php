<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlobalLovLearningBudget extends Migration
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
                    'lov_type_code' => 'ASSBDGT',
                    'key_data' => 'ATA',
                    'val_data' => 'Applied to All',
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
                    'lov_type_code' => 'ASSBDGT',
                    'key_data' => 'BGB',
                    'val_data' => 'By Budget group',
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
                    'lov_type_code' => 'CLCBDGT',
                    'key_data' => 'BPC',
                    'val_data' => 'Based on Payroll Component',
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
                    'lov_type_code' => 'CLCBDGT',
                    'key_data' => 'FA',
                    'val_data' => 'Fixed Amount',
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
                    'lov_type_code' => 'BDGTOWN',
                    'key_data' => 'ADM',
                    'val_data' => 'Admin',
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
                    'lov_type_code' => 'BDGTOWN',
                    'key_data' => 'HOU',
                    'val_data' => 'Head of Unit',
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
                    'lov_type_code' => 'BDGTSTT',
                    'key_data' => 'CAL',
                    'val_data' => 'Calculated',
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
                    'lov_type_code' => 'BDGTSTT',
                    'key_data' => 'REL',
                    'val_data' => 'Released',
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
                    'lov_type_code' => 'BDGTSTT',
                    'key_data' => 'REV',
                    'val_data' => 'Reviewed',
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
                    'lov_type_code' => 'BDGTSTT',
                    'key_data' => 'FIN',
                    'val_data' => 'Finalized',
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
            DB::table('lovs')->where('lov_type_code', '=', 'ASSBDGT')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'CLCBDGT')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'BDGTOWN')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'BDGTSTT')->delete();
        }
    }
}
