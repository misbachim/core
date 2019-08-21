<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use App\Jobs\PublishJob;
use App\Jobs\ConsumeJob;

class ReportController extends Controller
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function generateReport(Request $request)
    {
        dispatch(new PublishJob(
            env('CDN_MSG_QUEUE'),
            [
                'type' => Config::get('constant.SUBMIT_TASK'),
                'data' => [
                    'runDateTime' => $request->runDateTime,
                    'taskChain' => [
                        [
                            'type' => Config::get('constant.PROCESS_REPORT'),
                            'data' => [
                                'bundleName' => '',
                                'contents' => [],
                                'format' => ''
                            ]
                        ]
                    ]
                ]
            ]
        ));
    }
}
