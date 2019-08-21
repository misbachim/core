<?php
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AssignmentsTerminateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // get list company and tenant id
        $tenant_company = DB::table('assignment_reasons')
            ->select(
                'tenant_id',
                'company_id'
            )
            ->where([
                ['eff_begin', '<=', Carbon::now()],
                ['eff_end', '>=', Carbon::now()]
            ])
            ->distinct()
            ->get();

        foreach ($tenant_company as $tc) {

            // delete data with lov_acty TERM
            DB::table('assignment_reasons')->where([
                ['tenant_id', $tc->tenant_id],
                ['company_id', $tc->company_id],
                ['lov_acty', 'TERM']
            ])->delete();

            // insert new master data
            DB::table('assignment_reasons')->insert([
                [
                    'tenant_id' => $tc->tenant_id,
                    'company_id' => $tc->company_id,
                    'eff_begin' => Carbon::now(),
                    'eff_end' => '9999-12-31',
                    'lov_acty' => 'TERM',
                    'code' => 'LO',
                    'description' => 'Layoffs (PHK)',
                    'created_by' => 0,
                    'created_at' => Carbon::now(),
                    'updated_by' => null,
                    'updated_at' => null,
                ],
                [
                    'tenant_id' => $tc->tenant_id,
                    'company_id' => $tc->company_id,
                    'eff_begin' => Carbon::now(),
                    'eff_end' => '9999-12-31',
                    'lov_acty' => 'TERM',
                    'code' => 'RS',
                    'description' => 'Resign',
                    'created_by' => 0,
                    'created_at' => Carbon::now(),
                    'updated_by' => null,
                    'updated_at' => null,
                ],
                [
                    'tenant_id' => $tc->tenant_id,
                    'company_id' => $tc->company_id,
                    'eff_begin' => Carbon::now(),
                    'eff_end' => '9999-12-31',
                    'lov_acty' => 'TERM',
                    'code' => 'PTD',
                    'description' => 'Permanent Total Disability',
                    'created_by' => 0,
                    'created_at' => Carbon::now(),
                    'updated_by' => null,
                    'updated_at' => null,
                ],
                [
                    'tenant_id' => $tc->tenant_id,
                    'company_id' => $tc->company_id,
                    'eff_begin' => Carbon::now(),
                    'eff_end' => '9999-12-31',
                    'lov_acty' => 'TERM',
                    'code' => 'RETAG',
                    'description' => 'Retirement age',
                    'created_by' => 0,
                    'created_at' => Carbon::now(),
                    'updated_by' => null,
                    'updated_at' => null,
                ],
                [
                    'tenant_id' => $tc->tenant_id,
                    'company_id' => $tc->company_id,
                    'eff_begin' => Carbon::now(),
                    'eff_end' => '9999-12-31',
                    'lov_acty' => 'TERM',
                    'code' => 'DTH',
                    'description' => 'Death',
                    'created_by' => 0,
                    'created_at' => Carbon::now(),
                    'updated_by' => null,
                    'updated_at' => null,
                ],
                [
                    'tenant_id' => $tc->tenant_id,
                    'company_id' => $tc->company_id,
                    'eff_begin' => Carbon::now(),
                    'eff_end' => '9999-12-31',
                    'lov_acty' => 'TERM',
                    'code' => 'DWA',
                    'description' => 'Died in work accident',
                    'created_by' => 0,
                    'created_at' => Carbon::now(),
                    'updated_by' => null,
                    'updated_at' => null,
                ],
                [
                    'tenant_id' => $tc->tenant_id,
                    'company_id' => $tc->company_id,
                    'eff_begin' => Carbon::now(),
                    'eff_end' => '9999-12-31',
                    'lov_acty' => 'TERM',
                    'code' => 'LIF',
                    'description' => 'Leaving the territory of Indonesia forever',
                    'created_by' => 0,
                    'created_at' => Carbon::now(),
                    'updated_by' => null,
                    'updated_at' => null,
                ]
            ]);
        }

        // Set ID sequence to the correct value.
        DB::select("SELECT setval('assignment_reasons_id_seq', (SELECT max(id) + 1 FROM assignment_reasons))");
    }
}