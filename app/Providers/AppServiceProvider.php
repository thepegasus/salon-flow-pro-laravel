<?php

namespace App\Providers;

use App\Repositories\Contracts\MainDomainRepositoryInterface;
use App\Repositories\Contracts\StaffLeaveRequestRepositoryInterface;
use App\Repositories\Contracts\StaffProfileRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Repositories\Eloquent\MainDomainRepository;
use App\Repositories\Eloquent\StaffLeaveRequestRepository;
use App\Repositories\Eloquent\StaffProfileRepository;
use App\Repositories\Eloquent\TenantRepository;
use App\Services\TenantContext;
use App\Services\TenantUrl;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(TenantUrl::class);

        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
        $this->app->bind(MainDomainRepositoryInterface::class, MainDomainRepository::class);
        $this->app->bind(StaffProfileRepositoryInterface::class, StaffProfileRepository::class);
        $this->app->bind(StaffLeaveRequestRepositoryInterface::class, StaffLeaveRequestRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
