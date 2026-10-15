<?php

use Illuminate\Support\Facades\Route;

// Dashboard
use App\Http\Controllers\Backend\Dashboard\DashboardAdminController;

// My profile
use App\Http\Controllers\Backend\MyProfile\AccountController;
use App\Http\Controllers\Backend\MyProfile\ActivityController;
use App\Http\Controllers\Backend\MyProfile\LoginSessionController;
use App\Http\Controllers\Backend\MyProfile\ProfileController;
use App\Http\Controllers\Backend\MyProfile\SecurityController;

// User management
use App\Http\Controllers\Backend\UserManagement\RoleController;
use App\Http\Controllers\Backend\UserManagement\UserController;

// Help / system
use App\Http\Controllers\Backend\Help\LogActivityController;
use App\Http\Controllers\Backend\Settings\SettingController;

// Mind OS
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MindOs\ActionPlanController;
use App\Http\Controllers\MindOs\AudioController;
use App\Http\Controllers\MindOs\BookingController;
use App\Http\Controllers\MindOs\BugDetectorController;
use App\Http\Controllers\MindOs\CertificationController;
use App\Http\Controllers\MindOs\CommunityController;
use App\Http\Controllers\MindOs\EvaluationController;
use App\Http\Controllers\MindOs\FoundationController;
use App\Http\Controllers\MindOs\JournalController;
use App\Http\Controllers\MindOs\JourneyController;
use App\Http\Controllers\MindOs\PractitionerController;
use App\Http\Controllers\MindOs\SixRController;
use App\Http\Controllers\MindOs\Admin\BookingManageController;
use App\Http\Controllers\MindOs\Admin\ContentController;
use App\Http\Controllers\MindOs\Admin\EnrollmentManageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public (SEO) ---
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [LandingController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [LandingController::class, 'robots'])->name('robots');
Route::redirect('/login', '/admin/login', 301);
Route::redirect('/daftar', '/admin/register', 301);
Route::redirect('/register', '/admin/register', 301);

Route::middleware(['auth', 'forbid-banned-user'])->group(function () {

    // --- Dashboard (every authenticated role) ---
    Route::get('/admin/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard');

    // --- My account (every authenticated user) ---
    Route::get('/admin/my-account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/admin/my-account/{id}/avatar', [AccountController::class, 'editAvatar'])->name('avatar-edit');

    Route::get('/admin/my-activity', [ActivityController::class, 'index'])->name('my-activity.index');
    Route::get('/admin/mget-my-activity', [ActivityController::class, 'getActivity'])->name('get-my-activity');

    Route::get('/admin/mmy-login-session', [LoginSessionController::class, 'index'])->name('my-login-session.index');
    Route::get('/admin/mget-my-login-session', [LoginSessionController::class, 'getLoginSession'])->name('get-my-login-session');

    // --- Activity log ---
    // Accessible to every authenticated user; the controller scopes the query
    // so a non-Superadmin only ever sees the entries they caused.
    Route::get('/admin/log-activity', [LogActivityController::class, 'index'])->name('log-activity.index');
    Route::get('/admin/get-datalogactivity', [LogActivityController::class, 'getDataLogActivity'])->name('get-datalogactivity');
    Route::get('/admin/log-activity/{id}/detail', [LogActivityController::class, 'detail'])->name('log-activity.detail');
    Route::get('/admin/log-activity/{id}', [LogActivityController::class, 'show'])->name('log-activity.show');

    // Shared helpers used by the role/user forms.
    Route::get('/admin/select/role', [RoleController::class, 'select'])->name('role.select');

    /*
    |----------------------------------------------------------------------
    | Write operations — tighter rate limit than plain page reads.
    |----------------------------------------------------------------------
    */
    Route::middleware('throttle:write')->group(function () {

        Route::post('/admin/my-account/{id}/update-avatar', [AccountController::class, 'updateAvatar'])->name('avatar-update');
        Route::post('/admin/my-security', [SecurityController::class, 'store'])->name('change.password');
        Route::post('/admin/my-security/logout-other-devices', [SecurityController::class, 'logoutOtherDevices'])
            ->name('security.logout-other-devices');

        // --- Settings (Superadmin) ---
        Route::middleware('can:view_resources')->group(function () {
            Route::post('/admin/settings/update', [SettingController::class, 'update'])->name('settings.update');
            Route::post('/admin/settings/regenerate-branding', [SettingController::class, 'regenerateBranding'])
                ->name('settings.branding');

            Route::post('/admin/roles/generate-permissions', [RoleController::class, 'generatePermissions'])->name('roles.generate');
            Route::post('/admin/users/mass-delete', [UserController::class, 'massDelete'])->name('users.mass-delete');
            Route::post('/admin/users/{id}/ban', [UserController::class, 'ban'])->name('users.ban');
            Route::post('/admin/users/{id}/unban', [UserController::class, 'unban'])->name('users.unban');
            Route::post('/admin/roles/mass-delete', [RoleController::class, 'massDelete'])->name('roles.mass-delete');
        });
    });

    // --- Profile & security pages (resource controllers keep their own verbs) ---
    Route::resource('/admin/my-profile', ProfileController::class);
    Route::resource('/admin/my-security', SecurityController::class);

    // --- Settings page (read) ---
    Route::get('/admin/settings', [SettingController::class, 'index'])
        ->middleware('can:view_resources')
        ->name('settings.index');

    /*
    |----------------------------------------------------------------------
    | User & role management — Superadmin only.
    |----------------------------------------------------------------------
    */
    Route::middleware('can:view_resources')->group(function () {
        Route::resource('/admin/users', UserController::class);
        Route::get('/admin/get-datauser', [UserController::class, 'getDataUsers'])->name('get-users');
        Route::get('/admin/get-user-show-log/{id}', [UserController::class, 'getLoginSession'])->name('get-user-show-log');
        Route::get('/admin/get-user-show-log-activity/{id}', [UserController::class, 'getActivity'])->name('get-user-show-log-activity');

        Route::resource('/admin/roles', RoleController::class);
        Route::get('/admin/get-datarole', [RoleController::class, 'getDataRoles'])->name('get-datarole');
    });
});

/*
|--------------------------------------------------------------------------
| Mind OS member area (/app) — every authenticated user.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'forbid-banned-user'])->prefix('app')->name('mindos.')->group(function () {

    Route::get('/', [JourneyController::class, 'index'])->name('journey');

    Route::get('/foundation', [FoundationController::class, 'index'])->name('foundation.index');
    Route::get('/foundation/{module}', [FoundationController::class, 'show'])->name('foundation.show');

    Route::get('/cek-pikiran', [BugDetectorController::class, 'index'])->name('bug-detector.index');
    Route::get('/cek-pikiran/hasil/{assessment}', [BugDetectorController::class, 'show'])->name('bug-detector.show');

    Route::get('/jurnal', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/jurnal/tulis', [JournalController::class, 'create'])->name('journal.create');
    Route::get('/jurnal/{journal}', [JournalController::class, 'show'])->name('journal.show');

    Route::get('/audio', [AudioController::class, 'index'])->name('audio.index');
    Route::get('/audio/{track}', [AudioController::class, 'show'])->name('audio.show');

    Route::get('/6r-tracker', [SixRController::class, 'index'])->name('six-r.index');
    Route::get('/action-plan', [ActionPlanController::class, 'index'])->name('action-plan.index');

    Route::get('/evaluasi', [EvaluationController::class, 'index'])->name('evaluation.index');
    Route::get('/evaluasi/baru', [EvaluationController::class, 'create'])->name('evaluation.create');

    Route::get('/komunitas', [CommunityController::class, 'index'])->name('community.index');
    Route::get('/komunitas/{post}', [CommunityController::class, 'show'])->name('community.show');

    Route::get('/layanan', [BookingController::class, 'services'])->name('services.index');
    Route::get('/layanan/{service}/booking', [BookingController::class, 'create'])->name('bookings.create');
    Route::get('/booking-saya', [BookingController::class, 'index'])->name('bookings.index');

    Route::get('/sertifikasi', [CertificationController::class, 'index'])->name('certification.index');

    Route::middleware('throttle:write')->group(function () {
        Route::post('/foundation/{module}/selesai', [FoundationController::class, 'complete'])->name('foundation.complete');
        Route::post('/cek-pikiran', [BugDetectorController::class, 'store'])->name('bug-detector.store');
        Route::post('/jurnal', [JournalController::class, 'store'])->name('journal.store');
        Route::delete('/jurnal/{journal}', [JournalController::class, 'destroy'])->name('journal.destroy');
        Route::post('/audio/{track}/listen', [AudioController::class, 'listen'])->name('audio.listen');
        Route::post('/6r-tracker', [SixRController::class, 'store'])->name('six-r.store');
        Route::delete('/6r-tracker/{log}', [SixRController::class, 'destroy'])->name('six-r.destroy');
        Route::post('/action-plan', [ActionPlanController::class, 'store'])->name('action-plan.store');
        Route::post('/action-plan/hari/{day}', [ActionPlanController::class, 'toggleDay'])->name('action-plan.day');
        Route::post('/action-plan/{plan}/stop', [ActionPlanController::class, 'stop'])->name('action-plan.stop');
        Route::post('/evaluasi', [EvaluationController::class, 'store'])->name('evaluation.store');
        Route::post('/komunitas', [CommunityController::class, 'store'])->name('community.store');
        Route::post('/komunitas/{post}/komentar', [CommunityController::class, 'comment'])->name('community.comment');
        Route::post('/komunitas/{post}/like', [CommunityController::class, 'like'])->name('community.like');
        Route::post('/komunitas/{post}/pin', [CommunityController::class, 'pin'])->name('community.pin');
        Route::delete('/komunitas/{post}', [CommunityController::class, 'destroy'])->name('community.destroy');
        Route::post('/layanan/{service}/booking', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('/booking-saya/{booking}/batal', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/sertifikasi/{program}/daftar', [CertificationController::class, 'apply'])->name('certification.apply');
    });

    // --- Practitioner / Trainer ---
    Route::middleware('can:mindos.practice')->group(function () {
        Route::get('/sesi-klien', [PractitionerController::class, 'index'])->name('practitioner.index');
        Route::put('/sesi-klien/{booking}', [PractitionerController::class, 'update'])
            ->middleware('throttle:write')->name('practitioner.update');
    });

    // --- Manajemen Mind OS (admin) ---
    Route::middleware('can:mindos.manage')->prefix('kelola')->name('admin.')->group(function () {
        Route::get('/booking', [BookingManageController::class, 'index'])->name('bookings.index');
        Route::get('/sertifikasi', [EnrollmentManageController::class, 'index'])->name('enrollments.index');
        Route::get('/konten/{type}', [ContentController::class, 'index'])->name('content.index');
        Route::get('/konten/{type}/baru', [ContentController::class, 'create'])->name('content.create');
        Route::get('/konten/{type}/{id}/edit', [ContentController::class, 'edit'])->whereNumber('id')->name('content.edit');

        Route::middleware('throttle:write')->group(function () {
            Route::put('/booking/{booking}', [BookingManageController::class, 'update'])->name('bookings.update');
            Route::put('/sertifikasi/{enrollment}', [EnrollmentManageController::class, 'update'])->name('enrollments.update');
            Route::post('/konten/{type}', [ContentController::class, 'store'])->name('content.store');
            Route::put('/konten/{type}/{id}', [ContentController::class, 'update'])->whereNumber('id')->name('content.update');
            Route::delete('/konten/{type}/{id}', [ContentController::class, 'destroy'])->whereNumber('id')->name('content.destroy');
        });
    });
});

require __DIR__ . '/auth.php';
