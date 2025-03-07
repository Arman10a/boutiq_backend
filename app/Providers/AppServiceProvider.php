<?php

namespace App\Providers;

use App\Http\Contracts\BookingRepositoryInterface;
use App\Http\Contracts\ProductsInterface;
use App\Http\Contracts\UserRepositoryInterface;
use App\Http\Repositories\BookingRepository;
use App\Http\Repositories\ProductsRepository;
use App\Http\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Models\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            UserRepositoryInterface::class,
            UserRepository::class
            );
        $this->app->singleton(
            BookingRepositoryInterface::class,
            BookingRepository::class
        );
        $this->app->singleton(
            ProductsInterface::class,
            ProductsRepository::class
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
