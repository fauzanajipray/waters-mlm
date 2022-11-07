<?php

namespace App\Providers;

use App\Http\Controllers\Admin\RoleCrudController;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Controllers\UserCrudController::class,
            \App\Http\Controllers\Admin\UserCrudController::class,
        );

        $this->app->bind(
            \Backpack\PermissionManager\app\Http\Controllers\RoleCrudController::class,
            \App\Http\Controllers\Admin\RoleCrudController::class,
        );
        

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
