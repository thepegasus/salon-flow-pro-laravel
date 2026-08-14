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

/**
 * Registers the full tenant admin panel's routes. Called twice: once under
 * the {subdomain}.mainDomain domain (bare names, e.g. "services.index"),
 * and once under mainDomain/{slug} (each name suffixed ".bySlug", e.g.
 * "services.index.bySlug") so TenantUrl::route() can resolve either style
 * from a single call site without callers needing to know which one is
 * active for the current request.
 */
$registerTenantRoutes = function (string $nameSuffix = ''): void {
    Route::get('/login', [LoginController::class, 'create'])->name("login{$nameSuffix}");
    Route::post('/login', [LoginController::class, 'store'])->name("login.store{$nameSuffix}");
    Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name("logout{$nameSuffix}");

    Route::middleware('auth')->group(function () use ($nameSuffix): void {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name("tenant.dashboard{$nameSuffix}");

        Route::middleware('permission:staff.view')->group(function () use ($nameSuffix): void {
            Route::get('/staff', [StaffsController::class, 'index'])->name("staff.index{$nameSuffix}");
            Route::get('/staff/leave-requests', [StaffLeaveRequestsController::class, 'index'])->name("staff.leaveRequests.index{$nameSuffix}");
        });

        Route::middleware('permission:staff.create')->group(function () use ($nameSuffix): void {
            Route::get('/staff/create', [StaffsController::class, 'create'])->name("staff.create{$nameSuffix}");
            Route::post('/staff', [StaffsController::class, 'store'])->name("staff.store{$nameSuffix}");
        });

        Route::middleware('permission:staff.edit')->group(function () use ($nameSuffix): void {
            Route::get('/staff/{staff}/edit', [StaffsController::class, 'edit'])->name("staff.edit{$nameSuffix}");
            Route::put('/staff/{staff}', [StaffsController::class, 'update'])->name("staff.update{$nameSuffix}");
            Route::post('/staff/leave-requests', [StaffLeaveRequestsController::class, 'store'])->name("staff.leaveRequests.store{$nameSuffix}");
            Route::put('/staff/leave-requests/{leaveRequest}', [StaffLeaveRequestsController::class, 'update'])->name("staff.leaveRequests.update{$nameSuffix}");
        });

        Route::middleware('permission:staff.delete')->group(function () use ($nameSuffix): void {
            Route::delete('/staff/{staff}', [StaffsController::class, 'destroy'])->name("staff.destroy{$nameSuffix}");
        });

        Route::middleware('permission:staff.view')->group(function () use ($nameSuffix): void {
            Route::get('/staff/{staff}', [StaffsController::class, 'show'])->name("staff.show{$nameSuffix}");
        });

        Route::middleware('permission:services.view')->group(function () use ($nameSuffix): void {
            Route::get('/services', [ServicesController::class, 'index'])->name("services.index{$nameSuffix}");
            Route::get('/services/categories', [ServiceCategoriesController::class, 'index'])->name("serviceCategories.index{$nameSuffix}");
        });

        Route::middleware('permission:services.create')->group(function () use ($nameSuffix): void {
            Route::get('/services/create', [ServicesController::class, 'create'])->name("services.create{$nameSuffix}");
            Route::post('/services', [ServicesController::class, 'store'])->name("services.store{$nameSuffix}");
            Route::get('/services/categories/create', [ServiceCategoriesController::class, 'create'])->name("serviceCategories.create{$nameSuffix}");
            Route::post('/services/categories', [ServiceCategoriesController::class, 'store'])->name("serviceCategories.store{$nameSuffix}");
        });

        Route::middleware('permission:services.edit')->group(function () use ($nameSuffix): void {
            Route::get('/services/{service}/edit', [ServicesController::class, 'edit'])->name("services.edit{$nameSuffix}");
            Route::put('/services/{service}', [ServicesController::class, 'update'])->name("services.update{$nameSuffix}");
            Route::get('/services/categories/{category}/edit', [ServiceCategoriesController::class, 'edit'])->name("serviceCategories.edit{$nameSuffix}");
            Route::put('/services/categories/{category}', [ServiceCategoriesController::class, 'update'])->name("serviceCategories.update{$nameSuffix}");
        });

        Route::middleware('permission:services.delete')->group(function () use ($nameSuffix): void {
            Route::delete('/services/{service}', [ServicesController::class, 'destroy'])->name("services.destroy{$nameSuffix}");
            Route::delete('/services/categories/{category}', [ServiceCategoriesController::class, 'destroy'])->name("serviceCategories.destroy{$nameSuffix}");
        });

        Route::middleware('permission:services.view')->group(function () use ($nameSuffix): void {
            Route::get('/services/{service}', [ServicesController::class, 'show'])->name("services.show{$nameSuffix}");
        });

        Route::middleware('permission:appointments.view')->group(function () use ($nameSuffix): void {
            Route::get('/appointments', [AppointmentsController::class, 'index'])->name("appointments.index{$nameSuffix}");
            Route::get('/walk-ins', [WalkInsController::class, 'index'])->name("walkIns.index{$nameSuffix}");
        });

        Route::middleware('permission:appointments.create')->group(function () use ($nameSuffix): void {
            Route::get('/appointments/create', [AppointmentsController::class, 'create'])->name("appointments.create{$nameSuffix}");
            Route::post('/appointments', [AppointmentsController::class, 'store'])->name("appointments.store{$nameSuffix}");
            Route::post('/walk-ins', [WalkInsController::class, 'store'])->name("walkIns.store{$nameSuffix}");
        });

        Route::middleware('permission:appointments.edit')->group(function () use ($nameSuffix): void {
            Route::put('/appointments/{appointment}/reschedule', [AppointmentsController::class, 'reschedule'])->name("appointments.reschedule{$nameSuffix}");
            Route::put('/appointments/{appointment}/cancel', [AppointmentsController::class, 'cancel'])->name("appointments.cancel{$nameSuffix}");
            Route::put('/appointments/{appointment}/no-show', [AppointmentsController::class, 'noShow'])->name("appointments.noShow{$nameSuffix}");
            Route::put('/walk-ins/{walkIn}/assign', [WalkInsController::class, 'assign'])->name("walkIns.assign{$nameSuffix}");
        });

        Route::middleware('permission:appointments.view')->group(function () use ($nameSuffix): void {
            Route::get('/appointments/{appointment}', [AppointmentsController::class, 'show'])->name("appointments.show{$nameSuffix}");
        });

        Route::middleware('permission:clients.view')->group(function () use ($nameSuffix): void {
            Route::get('/clients', [ClientsController::class, 'index'])->name("clients.index{$nameSuffix}");
        });

        Route::middleware('permission:clients.create')->group(function () use ($nameSuffix): void {
            Route::get('/clients/create', [ClientsController::class, 'create'])->name("clients.create{$nameSuffix}");
            Route::post('/clients', [ClientsController::class, 'store'])->name("clients.store{$nameSuffix}");
        });

        Route::middleware('permission:clients.edit')->group(function () use ($nameSuffix): void {
            Route::get('/clients/{client}/edit', [ClientsController::class, 'edit'])->name("clients.edit{$nameSuffix}");
            Route::put('/clients/{client}', [ClientsController::class, 'update'])->name("clients.update{$nameSuffix}");
        });

        Route::middleware('permission:clients.view')->group(function () use ($nameSuffix): void {
            Route::get('/clients/{client}', [ClientsController::class, 'show'])->name("clients.show{$nameSuffix}");
        });

        Route::middleware('permission:appointments.view')->group(function () use ($nameSuffix): void {
            Route::get('/bridal-engagements', [BridalEngagementsController::class, 'index'])->name("bridalEngagements.index{$nameSuffix}");
        });

        Route::middleware('permission:appointments.create')->group(function () use ($nameSuffix): void {
            Route::get('/bridal-engagements/create', [BridalEngagementsController::class, 'create'])->name("bridalEngagements.create{$nameSuffix}");
            Route::post('/bridal-engagements', [BridalEngagementsController::class, 'store'])->name("bridalEngagements.store{$nameSuffix}");
        });

        Route::middleware('permission:appointments.view')->group(function () use ($nameSuffix): void {
            Route::get('/bridal-engagements/{bridalEngagement}', [BridalEngagementsController::class, 'show'])->name("bridalEngagements.show{$nameSuffix}");
        });

        Route::middleware('permission:billing.view')->group(function () use ($nameSuffix): void {
            Route::get('/bills', [BillsController::class, 'index'])->name("bills.index{$nameSuffix}");
        });

        Route::middleware('permission:billing.create')->group(function () use ($nameSuffix): void {
            Route::get('/bills/quick', [QuickBillController::class, 'create'])->name("bills.quick.create{$nameSuffix}");
            Route::get('/bills/quick/services/{code}', [QuickBillController::class, 'lookupService'])->name("bills.quick.lookupService{$nameSuffix}");
            Route::get('/bills/quick/clients/{phone}', [QuickBillController::class, 'lookupClient'])->name("bills.quick.lookupClient{$nameSuffix}");
            Route::post('/bills/quick/settle', [QuickBillController::class, 'settle'])->name("bills.quick.settle{$nameSuffix}");

            Route::post('/appointments/{appointment}/bill', [BillsController::class, 'generateFromAppointment'])->name("bills.generateFromAppointment{$nameSuffix}");
            Route::post('/bills', [BillsController::class, 'storeManual'])->name("bills.storeManual{$nameSuffix}");
            Route::put('/bills/{bill}/payments', [BillsController::class, 'recordPayment'])->name("bills.recordPayment{$nameSuffix}");
        });

        Route::middleware('permission:billing.edit')->group(function () use ($nameSuffix): void {
            Route::put('/bills/{bill}/refund', [BillsController::class, 'refund'])->name("bills.refund{$nameSuffix}");
        });

        Route::middleware('permission:billing.view')->group(function () use ($nameSuffix): void {
            Route::get('/bills/{bill}', [BillsController::class, 'show'])->name("bills.show{$nameSuffix}");
        });
    });
};

Route::domain('admin.'.$mainDomain)->middleware('super_admin.only')->group(function (): void {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('superAdmin.dashboard');
});

Route::domain('{subdomain}.'.$mainDomain)->middleware('tenant.only')->group(function () use ($registerTenantRoutes): void {
    Route::get('/', function () {
        $destination = auth()->check() ? '/dashboard' : '/login';

        return redirect()->to(request()->getSchemeAndHttpHost().$destination);
    })->name('tenant.root');

    $registerTenantRoutes();
});

Route::domain($mainDomain)->group(function () use ($registerTenantRoutes): void {
    Route::middleware('super_admin.only')->get('/admin', [SuperAdminDashboardController::class, 'index'])
        ->name('superAdmin.dashboard.byPath');

    Route::middleware('tenant.only')->prefix('/{slug}')->where(['slug' => '(?!admin$|login$|register$)[a-z0-9-]+'])->group(function () use ($registerTenantRoutes): void {
        Route::middleware('auth')->get('/', [TenantDashboardController::class, 'index'])->name('tenant.dashboard.bySlugRoot');

        $registerTenantRoutes('.bySlug');
    });

    Route::get('/', function () {
        return view('welcome');
    })->name('landing');
});
