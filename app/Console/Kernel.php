<?php

namespace App\Console;

use App\Jobs\AutoAnswerRequestsJob;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Events\IncomingMessageEvent;

// use App\Jobs\AutoAnswerRequestsJob;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 'App\Console\Commands\RunRPCServer'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // USING RabbitMQ Back

        $schedule->call(function () {
            receive('core', '*', '*', function ($msg) {
                event(new IncomingMessageEvent($msg));
            });
        })->name('listening')->everyMinute();

        $schedule->call(function () {
            $job = (new AutoAnswerRequestsJob());
            dispatch($job);
        })
            ->name('auto-answer-request')
            ->twiceDaily(2, 7)
            ->timezone('Asia/Bangkok');

        // RPC server should only be rerun when it fails.
        // $schedule->command('rpcserver:run')->name('rpc-server')
        //     ->everyMinute()->withoutOverlapping(52560000); // without overlapping for 100 years

        // $schedule->job(new AutoAnswerRequestsJob)->name('auto-answer-requests')->daily();
    }
}
