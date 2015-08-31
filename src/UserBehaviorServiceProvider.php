<?php

namespace HazeDevelopment;

use Illuminate\Support\ServiceProvider;

class UserBehaviorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/userbehavior.php' => config_path('userbehavior.php')
        ], 'config');
        
        require __DIR__.'/routes.php';
        UserBehavior::init();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
