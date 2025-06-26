<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\AuthLoggingService;
use App\Services\DictionaryService;
use App\Models\AuthLog;
use App\Observers\AuthLogObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Service bindings
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(UserRepositoryInterface::class),
                $app->make(OtpService::class),
                $app->make(AuthLoggingService::class)
            );
        });

        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService();
        });

        $this->app->singleton(AuthLoggingService::class, function ($app) {
            return new AuthLoggingService($app->make('request'));
        });

        $this->app->singleton(DictionaryService::class, function ($app) {
            return new DictionaryService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        AuthLog::observe(AuthLogObserver::class);
    }
}
