<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlobalLovLearning extends Migration
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
                    'lov_type_code' => 'INSTYPE',
                    'key_data' => 'IN',
                    'val_data' => 'Internal',
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
                    'lov_type_code' => 'INSTYPE',
                    'key_data' => 'EX',
                    'val_data' => 'External',
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
                    'lov_type_code' => 'CALCMTD',
                    'key_data' => 'FR',
                    'val_data' => 'Flat Rate',
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
                    'lov_type_code' => 'CALCMTD',
                    'key_data' => 'AS',
                    'val_data' => 'Activity Specific',
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
                    'lov_type_code' => 'UOMESUR',
                    'key_data' => 'HOURS',
                    'val_data' => 'Hours',
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
                    'lov_type_code' => 'UOMESUR',
                    'key_data' => 'DAYS',
                    'val_data' => 'Days',
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
                    'lov_type_code' => 'UOMESUR',
                    'key_data' => 'WEEKS',
                    'val_data' => 'Weeks',
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
                    'lov_type_code' => 'UOMESUR',
                    'key_data' => 'MONTHS',
                    'val_data' => 'Months',
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
                    'lov_type_code' => 'UOMESUR',
                    'key_data' => 'YEARS',
                    'val_data' => 'Years',
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
                    'lov_type_code' => 'INSSTAT',
                    'key_data' => 'PRIMARY',
                    'val_data' => 'Primary',
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
                    'lov_type_code' => 'INSSTAT',
                    'key_data' => 'BACKUP',
                    'val_data' => 'Back-up',
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
                    'lov_type_code' => 'INSSTAT',
                    'key_data' => 'ONCALL',
                    'val_data' => 'On call',
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
                    'lov_type_code' => 'PRORDER',
                    'key_data' => 'EARLY',
                    'val_data' => 'Earliest Registration Date',
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
                    'lov_type_code' => 'PRORDER',
                    'key_data' => 'REQUIRED',
                    'val_data' => 'Required then Earliest Registration Date',
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
                    'lov_type_code' => 'CONSTAT',
                    'key_data' => 'PROPOSED',
                    'val_data' => 'Proposed',
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
                    'lov_type_code' => 'CONSTAT',
                    'key_data' => 'ACTIVE',
                    'val_data' => 'Active',
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
                    'lov_type_code' => 'CONSTAT',
                    'key_data' => 'CANCEL',
                    'val_data' => 'Cancel',
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
                    'lov_type_code' => 'WAITLST',
                    'key_data' => 'MANUAL',
                    'val_data' => 'Manual Wait List',
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
                    'lov_type_code' => 'WAITLST',
                    'key_data' => 'AUTO',
                    'val_data' => 'Automatic Wait List',
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
            DB::table('lovs')->where('lov_type_code', '=', 'INSTYPE')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'CALCMTD')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'UOMESUR')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'INSSTAT')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'PRORDER')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'CONSTAT')->delete();
            DB::table('lovs')->where('lov_type_code', '=', 'WAITLST')->delete();
        }
    }
}
