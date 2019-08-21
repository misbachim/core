<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoAnswerRequestsJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        info('AutoAnswerRequestsJob: I am ready to auto-answer requests.');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info('AutoAnswerRequestsJob: Auto-answering requests...');

        $today = Carbon::today();

        DB::table('worklists')
            ->select(
                'worklists.id',
                'worklists.created_at as createdAt',
                'worklists.updated_at as updatedAt',
                'company_settings.setting_lov_key_data as setting'
            )
            ->join('company_settings', function ($join) {
                $join->on('company_settings.tenant_id', '=', 'worklists.tenant_id');
                $join->on('company_settings.company_id', '=', 'worklists.company_id');
            })
            ->whereNull('worklists.answer')
            ->where([
                ['worklists.is_active', true],
                ['company_settings.setting_type_code', 'WFES'],
                ['company_settings.setting_lov_key_data', '!=', 'NA']
            ])
            ->orderBy('worklists.id')
            ->chunk(1000, function ($workLists) use (&$today) {
                foreach ($workLists as $workList) {
                    $this->autoAnswerWorkList($workList, $today);
                }
            });

        info('AutoAnswerRequestsJob: Requests auto-answered.');
    }

    /**
     * Handle failures.
     *
     * @return void
     */
    public function failed(\Exception $ex)
    {
        info($ex);
    }

    private function autoAnswerWorkList($workList, $today)
    {
        $setting = explode('-', $workList->setting);
        $autoAnswerType = $setting[0];
        $days = intval($setting[1]);

        $chosenDate= $workList->updatedAt ? $workList->updatedAt : $workList->createdAt;
        $workListDate = Carbon::parse($chosenDate);

        if ($today->gt( $workListDate->addDays($days) )) {
            DB::table('worklists')
                ->where('id', $workList->id)
                ->update(['answer' => $autoAnswerType]);
        }
    }
}
