<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/18
 * Time: 10:32 下午.
 */

namespace HughCube\Laravel\ACM;

use HughCube\Laravel\ACM\Commands\SyncAppConfig;
use HughCube\Laravel\ACM\Commands\SyncConfig;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Boot the provider.
     */
    public function boot()
    {
        $source = realpath(dirname(__DIR__) . '/config/config.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('acm.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('acm');
        }
    }

    /**
     * Register the provider.
     */
    public function register()
    {
        $this->app->singleton('acm', function ($app) {
            $config = $app->make('config')->get('acm', []);
            return new Manager($config);
        });

        $this->registerCommand();
    }

    protected function registerCommand()
    {
        $this->commands([
            SyncAppConfig::class,
            SyncConfig::class
        ]);
    }
}
