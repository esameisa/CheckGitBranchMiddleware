<?php

namespace esameisa\CheckGitBranchMiddleware;

use Illuminate\Support\ServiceProvider;

class CheckGitBranchMiddlewareServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('checkgitbranch.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'checkgitbranch');
    }
}
