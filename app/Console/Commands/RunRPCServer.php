<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Business\RPC\RPCServer;

class RunRPCServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rpcserver:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run an RPC server in order to accept remote calls';

    /**
     * The RPC server.
     *
     * @var RPCServer
     */
    protected $rpcServer;

    /**
     * Create a new command instance.
     *
     * @param RPCServer $rpcServer
     * @return void
     */
    public function __construct(RPCServer $rpcServer)
    {
        parent::__construct();

        $this->rpcServer = $rpcServer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        info('Starting RPC Server...');

        // $this->rpcServer->start();

        info('Exited RPC Server.');
    }
}
