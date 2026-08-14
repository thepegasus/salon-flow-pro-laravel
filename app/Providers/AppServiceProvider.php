<?php

namespace App\Providers;

use App\Repositories\Contracts\AppointmentRepositoryInterface;
use App\Repositories\Contracts\BillRepositoryInterface;
use App\Repositories\Contracts\BridalEngagementRepositoryInterface;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Contracts\MainDomainRepositoryInterface;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Contracts\StaffLeaveRequestRepositoryInterface;
use App\Repositories\Contracts\StaffProfileRepositoryInterface;
use App\Repositories\Contracts\TenantRepositoryInterface;
use App\Repositories\Eloquent\AppointmentRepository;
use App\Repositories\Eloquent\BillRepository;
use App\Repositories\Eloquent\BridalEngagementRepository;
use App\Repositories\Eloquent\ClientRepository;
use App\Repositories\Eloquent\MainDomainRepository;
use App\Repositories\Eloquent\ServiceCategoryRepository;
use App\Repositories\Eloquent\ServiceRepository;
use App\Repositories\Eloquent\StaffLeaveRequestRepository;
use App\Repositories\Eloquent\StaffProfileRepository;
use App\Repositories\Eloquent\TenantRepository;
use App\Services\Contracts\ReminderChannelInterface;
use App\Services\LogReminderChannel;
use App\Services\TenantContext;
use App\Services\TenantUrl;
use Illuminate\Support\Facades\View;
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
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(ServiceCategoryRepositoryInterface::class, ServiceCategoryRepository::class);
        $this->app->bind(AppointmentRepositoryInterface::class, AppointmentRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(BridalEngagementRepositoryInterface::class, BridalEngagementRepository::class);
        $this->app->bind(BillRepositoryInterface::class, BillRepository::class);
        $this->app->bind(ReminderChannelInterface::class, LogReminderChannel::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view): void {
            $view->with('tenant', $this->app->make(TenantContext::class)->get());
        });

        View::composer('*', function ($view): void {
            $view->with('tenantUrl', $this->app->make(TenantUrl::class));
        });
    }
}
