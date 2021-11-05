<?php

namespace ImoTikuwa\ModelGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use ImoTikuwa\ModelGenerator\Console\Commands\SampleCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Initial startup process for all application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SampleCommand::class,
            ]);
        }
    }
}