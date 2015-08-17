<?php

namespace HazeDevelopment\UserBehavior;

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
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserBehavior::class);
    }
}
