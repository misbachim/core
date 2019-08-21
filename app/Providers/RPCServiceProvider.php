<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Business\RPC\RPCServer;
use App\Business\RPC\RPCClient;

class RPCServiceProvider extends ServiceProvider
{
    /**
     * Register any RPC services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('RPCServer', function ($app) {
            return new RPCServer();
        });

        $this->app->singleton('RPCClient', function ($app) {
            return new RPCClient();
        });
    }
}
