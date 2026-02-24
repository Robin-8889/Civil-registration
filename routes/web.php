<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BirthRecordController;
use App\Http\Controllers\MarriageRecordController;
use App\Http\Controllers\DeathRecordController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\RegistrationOfficeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\XMLReportController;
use App\Http\Controllers\VitalStatisticsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public home
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Public API Routes (for testing and external access)
Route::prefix('api/statistics')->name('api.statistics.')->group(function () {
    Route::get('/births/region', [VitalStatisticsController::class, 'birthStatisticsByRegion'])->name('births.region');
    Route::get('/deaths/age', [VitalStatisticsController::class, 'deathStatisticsByAge'])->name('deaths.age');
    Route::get('/marriages/region', [VitalStatisticsController::class, 'marriageStatisticsByRegion'])->name('marriages.region');
    Route::get('/population/demographics', [VitalStatisticsController::class, 'populationDemographics'])->name('population.demographics');
    Route::get('/annual-summary', [VitalStatisticsController::class, 'annualVitalSummary'])->name('annual-summary');
    Route::get('/birth-completeness', [VitalStatisticsController::class, 'birthRegistrationCompleteness'])->name('birth-completeness');
    Route::get('/certificates', [VitalStatisticsController::class, 'certificatesIssuedReport'])->name('certificates');
    Route::get('/dashboard', [VitalStatisticsController::class, 'dashboard'])->name('dashboard');
});

// Public XML Reports Routes
Route::prefix('reports/xml')->name('reports.xml.')->group(function () {
    Route::get('/vital-statistics', [XMLReportController::class, 'vitalStatisticsExport'])->name('vital_statistics');
    Route::get('/citizen/{birthRecord}', [XMLReportController::class, 'citizenReport'])->name('citizen');
    Route::get('/regional/{region}/{year?}', [XMLReportController::class, 'regionalStatistics'])->name('regional');
    Route::get('/monthly/{year}/{month}', [XMLReportController::class, 'monthlyReport'])->name('monthly');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile - Change Password
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // User Management - System Admin Only (no is_approved check needed, authorization handled in controller)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/disapprove', [UserController::class, 'disapprove'])->name('users.disapprove');
    Route::post('/users/{user}/grant-office-admin', [UserController::class, 'grantOfficeAdmin'])->name('users.grantOfficeAdmin');
    Route::post('/users/{user}/revoke-office-admin', [UserController::class, 'revokeOfficeAdmin'])->name('users.revokeOfficeAdmin');
    Route::post('/users/{user}/set-system-admin', [UserController::class, 'setSystemAdmin'])->name('users.setSystemAdmin');
    Route::post('/users/{user}/remove-system-admin', [UserController::class, 'removeSystemAdmin'])->name('users.removeSystemAdmin');

    // Registration Offices - System Admin Only (no is_approved check needed, authorization handled in controller)
    Route::resource('registration_offices', RegistrationOfficeController::class);

    // Protected data access routes - requires is_approved for registrar/clerk
    Route::middleware('check_user_approved')->group(function () {
        // Citizens Panel
        Route::get('/citizens', [CitizenController::class, 'index'])->name('citizens.index');

        // Birth Records - Office staff and system admin
        Route::resource('birth_records', BirthRecordController::class);

        // Marriage Records - Office staff and system admin
        Route::resource('marriage_records', MarriageRecordController::class);

        // Death Records - Office staff and system admin
        Route::resource('death_records', DeathRecordController::class);

        // Certificates - Office staff and system admin
        Route::resource('certificates', CertificateController::class);
        Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

        // (XML Reports routes moved to public section above)
    });
});

require __DIR__.'/auth.php';
