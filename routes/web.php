<?php

use App\Http\Controllers\StaffLeaveRequestsController;
use App\Http\Controllers\StaffsController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\TenantDashboardController;
use Illuminate\Support\Facades\Route;

$mainDomain = config('tenancy.main_domain');

Route::domain('admin.'.$mainDomain)->middleware('super_admin.only')->group(function (): void {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('superAdmin.dashboard');
});

Route::domain('{subdomain}.'.$mainDomain)->middleware('tenant.only')->group(function (): void {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');

    Route::middleware('auth')->group(function (): void {
        Route::middleware('permission:staff.view')->group(function (): void {
            Route::get('/staff', [StaffsController::class, 'index'])->name('staff.index');
            Route::get('/staff/leave-requests', [StaffLeaveRequestsController::class, 'index'])->name('staff.leaveRequests.index');
        });

        Route::middleware('permission:staff.create')->group(function (): void {
            Route::get('/staff/create', [StaffsController::class, 'create'])->name('staff.create');
            Route::post('/staff', [StaffsController::class, 'store'])->name('staff.store');
        });

        Route::middleware('permission:staff.edit')->group(function (): void {
            Route::get('/staff/{staff}/edit', [StaffsController::class, 'edit'])->name('staff.edit');
            Route::put('/staff/{staff}', [StaffsController::class, 'update'])->name('staff.update');
            Route::post('/staff/leave-requests', [StaffLeaveRequestsController::class, 'store'])->name('staff.leaveRequests.store');
            Route::put('/staff/leave-requests/{leaveRequest}', [StaffLeaveRequestsController::class, 'update'])->name('staff.leaveRequests.update');
        });

        Route::middleware('permission:staff.delete')->group(function (): void {
            Route::delete('/staff/{staff}', [StaffsController::class, 'destroy'])->name('staff.destroy');
        });

        Route::middleware('permission:staff.view')->group(function (): void {
            Route::get('/staff/{staff}', [StaffsController::class, 'show'])->name('staff.show');
        });
    });
});

Route::domain($mainDomain)->group(function (): void {
    Route::middleware('super_admin.only')->get('/admin', [SuperAdminDashboardController::class, 'index'])
        ->name('superAdmin.dashboard.byPath');

    Route::middleware('tenant.only')->prefix('/{slug}')->where(['slug' => '[a-z0-9-]+'])->group(function (): void {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard.bySlug');
    });

    Route::get('/', function () {
        return view('welcome');
    })->name('landing');
});
