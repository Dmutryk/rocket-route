<?php

namespace App\Providers;

use App\Http\Controllers\Api\LoginInterface;
use App\Http\Controllers\RocketLoginController;
use Illuminate\Support\ServiceProvider;

class RocketLoginProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(LoginInterface::class, RocketLoginController::class);
    }
}
