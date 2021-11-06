<?php

namespace ImoTikuwa\ModelGenerator\Providers;

use Illuminate\Support\ServiceProvider;
use ImoTikuwa\ModelGenerator\Console\Commands\{InfoCommand, ModelCommand};

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Initial startup process for all application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        $this->loadViewsFrom(dirname(__DIR__, 2) . DS . 'resources' . DS . 'views', 'tikuwa');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelCommand::class,
                InfoCommand::class,
            ]);
        }
    }
}
