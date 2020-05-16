<?php

namespace App\Providers;

use App\Http\Services\WorkerTokens;
use App\Http\Services\UserServices;
use App\Http\Services\NoteServices;
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
        $this->app->bind(UserServices::class, function () {
            return new UserServices();
        });
        $this->app->alias(UserServices::class, 'service.user.services');
        $this->app->bind(WorkerTokens::class, function () {
            return new WorkerTokens();
        });
        $this->app->alias(WorkerTokens::class, 'service.worker_tokens.services');
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
