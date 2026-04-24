<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OneTimePasswordController;
use App\Http\Controllers\Guest\ExplorePathController;

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ProfileController;

use App\Http\Controllers\Admin\KursusController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\QuizIntegrityController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\System\BackupController;
use App\Http\Controllers\User\CertificateController;
use App\Http\Controllers\User\ListKursusController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// [HOME] | Tampilan untuk halaman utama
Route::get('/', [ExplorePathController::class, 'landing'])->name('home.index');
Route::prefix('explore-your-path')->group(function () {
    Route::get('/', [ExplorePathController::class, 'questionnaire'])->name('explore.index');
    Route::post('/submit', [ExplorePathController::class, 'submit'])->name('explore.submit');
    Route::get('/result', [ExplorePathController::class, 'result'])->name('explore.result');
});

// [AUTH] | Grup route untuk authentikasi
Route::prefix('auth')->group(function () {
    /* Route untuk halaman authentikasi */
    Route::get('login', [AuthController::class, 'login_view'])->name('auth.view');
    Route::post('login', [AuthController::class, 'auth_login'])->name('auth.login');
    Route::post('logout', [AuthController::class, 'auth_logout'])->name('auth.logout');

    /* Route untuk halaman registrasi */
    Route::get('register', [AuthController::class, 'register_view'])->name('auth.register.view');
    Route::post('register', [AuthController::class, 'auth_register'])->name('auth.register');

    /* Route untuk halaman lupa kata sandi */
    Route::get('password-reset', [AuthController::class, 'password_reset_view'])->name('password.email');
    Route::post('password-reset', [AuthController::class, 'password_reset'])->name('password.reset');
    Route::get('password-reset/{token}', [AuthController::class, 'password_update_view'])->name('password.update.view');
    Route::post('password-reset/{token}', [AuthController::class, 'password_update'])->name('password.update');

    /* Route untuk halaman verifikasi kode OTP */
    Route::get('verifikasi', [OneTimePasswordController::class, 'otp_view'])->name('otp.view');
    Route::post('verifikasi', [OneTimePasswordController::class, 'otp_verification'])->name('otp.verification');
    Route::post('verifikasi/resend', [OneTimePasswordController::class, 'otp_resend'])->name('otp.resend');
});

// [USER] | Grup route untuk pengguna yang sudah terautentikasi
Route::middleware(['auth', 'check.access'])->group(function () {
    /* Tampilan untuk halaman dashboard pengguna */
    Route::get('dashboard', [DashboardController::class, 'index'])->name('user.dashboard');

    /* Route untuk membersihkan cache aplikasi */
    Route::get('clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        session()->flash('success_message', 'Seluruh cache aplikasi, termasuk konfigurasi, route, dan tampilan berhasil dibersihkan untuk memastikan sistem berjalan dengan optimal.');
        return redirect()->back();
    })->name('clear.cache');

    /* Route untuk mengatur tampilan profil pengguna */
    Route::prefix('profile')->group(function () {
        Route::get('{user}', [ProfileController::class, 'show'])->name('user.profile');
        Route::post('{user}/update-avatar', [ProfileController::class, 'update_avatar'])->name('user.profile.update_avatar');
        Route::post('{user}/update-profile', [ProfileController::class, 'update_profile'])->name('user.profile.update_profile');
        Route::post('{user}/update-password', [ProfileController::class, 'update_password'])->name('user.profile.update_password');

        Route::post('{user}/update-otp-setting', [ProfileController::class, 'update_otp_setting'])->name('user.profile.update_otp_setting');
        Route::post('{user}/update-email-setting', [ProfileController::class, 'update_email_setting'])->name('user.profile.update_email_setting');
        Route::post('{user}/update-whatsapp-setting', [ProfileController::class, 'update_whatsapp_setting'])->name('user.profile.update_whatsapp_setting');
    });

    Route::prefix('admin/kursus')->middleware('access:admin')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Route Modul Kursus (Ditaruh paling atas agar tidak bentrok dengan {kursus})
        |--------------------------------------------------------------------------
        */

        // CRUD Modul
        Route::post('modules/update', [ModuleController::class, 'update'])->name('admin.module.update');
        Route::post('modules/delete', [ModuleController::class, 'delete'])->name('admin.module.delete');
        Route::post('modules/update-order', [ModuleController::class, 'updateOrder'])->name('admin.module.updateOrder');

        // Detail modul
        Route::get('module/{module}/detail', [ModuleController::class, 'detail'])->name('admin.module.detail');

        // Store modul (ada kursus id)
        Route::post('{kursus}/modules/store', [ModuleController::class, 'store'])->name('admin.module.store');

        /*
        |--------------------------------------------------------------------------
        | Route Konten Modul
        |--------------------------------------------------------------------------
        */
        Route::get('content/{content}/quiz-data', [ContentController::class, 'quiz'])->name('admin.content.quiz');
        Route::post('module/{module}/content/store', [ContentController::class, 'store'])->name('admin.content.store');
        Route::post('content/update-order', [ContentController::class, 'updateOrder'])->name('admin.content.updateOrder');
        Route::post('content/update', [ContentController::class, 'update'])->name('admin.content.update');
        Route::post('content/delete', [ContentController::class, 'delete'])->name('admin.content.delete');
        Route::post('content/upload-image', [ContentController::class, 'uploadImage'])->name('admin.content.uploadImage');


        Route::get('integrity', [QuizIntegrityController::class, 'index'])->name('admin.kursus.integrity.index');
        Route::post('integrity/request', [QuizIntegrityController::class, 'request'])->name('admin.kursus.integrity.request');
        Route::get('integrity/{attempt}/detail', [QuizIntegrityController::class, 'detail'])->name('admin.kursus.integrity.detail');

        /*
        |--------------------------------------------------------------------------
        | Route Kursus
        |--------------------------------------------------------------------------
        */
        Route::get('/', [KursusController::class, 'index'])->name('admin.kursus.index');
        Route::get('create', [KursusController::class, 'create'])->name('admin.kursus.create');
        Route::post('store', [KursusController::class, 'store'])->name('admin.kursus.store');

        Route::get('{kursus}/edit', [KursusController::class, 'edit'])->name('admin.kursus.edit');
        Route::post('{kursus}/update', [KursusController::class, 'update'])->name('admin.kursus.update');

        Route::post('request/data', [KursusController::class, 'request'])->name('admin.kursus.request');
        Route::post('delete', [KursusController::class, 'delete'])->name('admin.kursus.delete');

        Route::get('{kursus}/peserta', [KursusController::class, 'peserta'])->name('admin.kursus.peserta');
        Route::post('{kursus}/peserta/request', [KursusController::class, 'pesertaRequest'])->name('admin.kursus.peserta.request');
    });

    Route::prefix('admin/pengguna')->middleware('access:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.user.index');
        Route::post('request', [UserController::class, 'request'])->name('admin.user.request');
        Route::get('{id}/detail', [UserController::class, 'detail'])->name('admin.user.detail');
        Route::post('store', [UserController::class, 'store'])->name('admin.user.store');
        Route::post('update', [UserController::class, 'update'])->name('admin.user.update');
        Route::post('delete', [UserController::class, 'delete'])->name('admin.user.delete');
        Route::post('toggle-status', [UserController::class, 'toggleStatus'])->name('admin.user.toggleStatus');
    });

    Route::prefix('system/backup')->middleware('access:admin')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('system.backup.index');
        Route::get('download', [BackupController::class, 'download'])->name('system.backup.download');
        Route::get('download/{filename}', [BackupController::class, 'downloadExisting'])->name('system.backup.download.existing');
        Route::get('list', [BackupController::class, 'list'])->name('system.backup.list');
        Route::delete('{filename}', [BackupController::class, 'deleteBackup'])->name('system.backup.delete');
    });

    Route::prefix('user/daftar-kursus')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Route Kursus Pengguna
        |--------------------------------------------------------------------------
        */
        Route::get('/', [ListKursusController::class, 'index'])->name('user.kursus.index');
        Route::get('{kursus}', [ListKursusController::class, 'show'])->name('user.kursus.show');
        Route::post('request', [ListKursusController::class, 'request'])->name('user.kursus.request');

        Route::post('{kursus}/enroll', [ListKursusController::class, 'enroll'])->name('user.kursus.enroll');
        Route::get('{kursus}/learn', [ListKursusController::class, 'learn'])->name('user.kursus.learn');

        Route::get('{kursus}/content/{content}', [ListKursusController::class, 'getContent'])->name('user.kursus.content');
        Route::post('{kursus}/content/{content}/complete', [ListKursusController::class, 'markContentComplete'])->name('user.kursus.content.complete');
        Route::post('{kursus}/quiz/{content}/start', [ListKursusController::class, 'startQuizAttempt'])->name('user.kursus.quiz.start');
        Route::post('{kursus}/quiz/{content}/integrity-log', [ListKursusController::class, 'logIntegrityViolation'])->name('user.kursus.quiz.integrity_log');
        Route::post('{kursus}/quiz/{content}/submit', [ListKursusController::class, 'submitQuiz'])->name('user.kursus.quiz.submit');
    });

    Route::prefix('user/certificate')->middleware('auth')->group(function () {
        Route::get('{userCourse}/preview', [CertificateController::class, 'preview'])->name('user.certificate.preview');
        Route::get('{userCourse}/show', [CertificateController::class, 'show'])->name('user.certificate.show');
        Route::get('{userCourse}/download', [CertificateController::class, 'download'])->name('user.certificate.download');
    });
});
