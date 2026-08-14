<?php

use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\BillsController;
use App\Http\Controllers\BridalEngagementsController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\QuickBillController;
use App\Http\Controllers\ServiceCategoriesController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\StaffLeaveRequestsController;
use App\Http\Controllers\StaffsController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\TenantDashboardController;
use App\Http\Controllers\WalkInsController;
use Illuminate\Support\Facades\Route;

$mainDomain = config('tenancy.main_domain');

Route::domain('admin.'.$mainDomain)->middleware('super_admin.only')->group(function (): void {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('superAdmin.dashboard');
});

Route::domain('{subdomain}.'.$mainDomain)->middleware('tenant.only')->group(function (): void {
    Route::get('/', function () {
        $destination = auth()->check() ? '/dashboard' : '/login';

        return redirect()->to(request()->getSchemeAndHttpHost().$destination);
    })->name('tenant.root');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::middleware('auth')->group(function (): void {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');

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

        Route::middleware('permission:services.view')->group(function (): void {
            Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
            Route::get('/services/categories', [ServiceCategoriesController::class, 'index'])->name('serviceCategories.index');
        });

        Route::middleware('permission:services.create')->group(function (): void {
            Route::get('/services/create', [ServicesController::class, 'create'])->name('services.create');
            Route::post('/services', [ServicesController::class, 'store'])->name('services.store');
            Route::get('/services/categories/create', [ServiceCategoriesController::class, 'create'])->name('serviceCategories.create');
            Route::post('/services/categories', [ServiceCategoriesController::class, 'store'])->name('serviceCategories.store');
        });

        Route::middleware('permission:services.edit')->group(function (): void {
            Route::get('/services/{service}/edit', [ServicesController::class, 'edit'])->name('services.edit');
            Route::put('/services/{service}', [ServicesController::class, 'update'])->name('services.update');
            Route::get('/services/categories/{category}/edit', [ServiceCategoriesController::class, 'edit'])->name('serviceCategories.edit');
            Route::put('/services/categories/{category}', [ServiceCategoriesController::class, 'update'])->name('serviceCategories.update');
        });

        Route::middleware('permission:services.delete')->group(function (): void {
            Route::delete('/services/{service}', [ServicesController::class, 'destroy'])->name('services.destroy');
            Route::delete('/services/categories/{category}', [ServiceCategoriesController::class, 'destroy'])->name('serviceCategories.destroy');
        });

        Route::middleware('permission:services.view')->group(function (): void {
            Route::get('/services/{service}', [ServicesController::class, 'show'])->name('services.show');
        });

        Route::middleware('permission:appointments.view')->group(function (): void {
            Route::get('/appointments', [AppointmentsController::class, 'index'])->name('appointments.index');
            Route::get('/walk-ins', [WalkInsController::class, 'index'])->name('walkIns.index');
        });

        Route::middleware('permission:appointments.create')->group(function (): void {
            Route::get('/appointments/create', [AppointmentsController::class, 'create'])->name('appointments.create');
            Route::post('/appointments', [AppointmentsController::class, 'store'])->name('appointments.store');
            Route::post('/walk-ins', [WalkInsController::class, 'store'])->name('walkIns.store');
        });

        Route::middleware('permission:appointments.edit')->group(function (): void {
            Route::put('/appointments/{appointment}/reschedule', [AppointmentsController::class, 'reschedule'])->name('appointments.reschedule');
            Route::put('/appointments/{appointment}/cancel', [AppointmentsController::class, 'cancel'])->name('appointments.cancel');
            Route::put('/appointments/{appointment}/no-show', [AppointmentsController::class, 'noShow'])->name('appointments.noShow');
            Route::put('/walk-ins/{walkIn}/assign', [WalkInsController::class, 'assign'])->name('walkIns.assign');
        });

        Route::middleware('permission:appointments.view')->group(function (): void {
            Route::get('/appointments/{appointment}', [AppointmentsController::class, 'show'])->name('appointments.show');
        });

        Route::middleware('permission:clients.view')->group(function (): void {
            Route::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
        });

        Route::middleware('permission:clients.create')->group(function (): void {
            Route::get('/clients/create', [ClientsController::class, 'create'])->name('clients.create');
            Route::post('/clients', [ClientsController::class, 'store'])->name('clients.store');
        });

        Route::middleware('permission:clients.edit')->group(function (): void {
            Route::get('/clients/{client}/edit', [ClientsController::class, 'edit'])->name('clients.edit');
            Route::put('/clients/{client}', [ClientsController::class, 'update'])->name('clients.update');
        });

        Route::middleware('permission:clients.view')->group(function (): void {
            Route::get('/clients/{client}', [ClientsController::class, 'show'])->name('clients.show');
        });

        Route::middleware('permission:appointments.view')->group(function (): void {
            Route::get('/bridal-engagements', [BridalEngagementsController::class, 'index'])->name('bridalEngagements.index');
        });

        Route::middleware('permission:appointments.create')->group(function (): void {
            Route::get('/bridal-engagements/create', [BridalEngagementsController::class, 'create'])->name('bridalEngagements.create');
            Route::post('/bridal-engagements', [BridalEngagementsController::class, 'store'])->name('bridalEngagements.store');
        });

        Route::middleware('permission:appointments.view')->group(function (): void {
            Route::get('/bridal-engagements/{bridalEngagement}', [BridalEngagementsController::class, 'show'])->name('bridalEngagements.show');
        });

        Route::middleware('permission:billing.view')->group(function (): void {
            Route::get('/bills', [BillsController::class, 'index'])->name('bills.index');
        });

        Route::middleware('permission:billing.create')->group(function (): void {
            Route::get('/bills/quick', [QuickBillController::class, 'create'])->name('bills.quick.create');
            Route::get('/bills/quick/services/{code}', [QuickBillController::class, 'lookupService'])->name('bills.quick.lookupService');
            Route::get('/bills/quick/clients/{phone}', [QuickBillController::class, 'lookupClient'])->name('bills.quick.lookupClient');
            Route::post('/bills/quick/settle', [QuickBillController::class, 'settle'])->name('bills.quick.settle');

            Route::post('/appointments/{appointment}/bill', [BillsController::class, 'generateFromAppointment'])->name('bills.generateFromAppointment');
            Route::post('/bills', [BillsController::class, 'storeManual'])->name('bills.storeManual');
            Route::put('/bills/{bill}/payments', [BillsController::class, 'recordPayment'])->name('bills.recordPayment');
        });

        Route::middleware('permission:billing.edit')->group(function (): void {
            Route::put('/bills/{bill}/refund', [BillsController::class, 'refund'])->name('bills.refund');
        });

        Route::middleware('permission:billing.view')->group(function (): void {
            Route::get('/bills/{bill}', [BillsController::class, 'show'])->name('bills.show');
        });
    });
});

Route::domain($mainDomain)->group(function (): void {
    Route::middleware('super_admin.only')->get('/admin', [SuperAdminDashboardController::class, 'index'])
        ->name('superAdmin.dashboard.byPath');

    Route::middleware(['tenant.only', 'auth'])->prefix('/{slug}')->where(['slug' => '(?!admin$|login$|register$)[a-z0-9-]+'])->group(function (): void {
        Route::get('/', [TenantDashboardController::class, 'index'])->name('tenant.dashboard.bySlugRoot');
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard.bySlug');
    });

    Route::get('/', function () {
        return view('welcome');
    })->name('landing');
});
