<?php

namespace App\Providers;

use App\Contracts\Repositories\CompanyRepositoryInterface;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Repositories\CompanyRepository;
use App\Repositories\TaskRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем биндинги интерфейсов репозиториев к их реализациям
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}

