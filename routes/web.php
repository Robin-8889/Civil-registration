<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BirthRecordController;
use App\Http\Controllers\MarriageRecordController;
use App\Http\Controllers\DeathRecordController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\RegistrationOfficeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public home
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
        // Birth Records - Office staff and system admin
        Route::resource('birth_records', BirthRecordController::class);

        // Marriage Records - Office staff and system admin
        Route::resource('marriage_records', MarriageRecordController::class);

        // Death Records - Office staff and system admin
        Route::resource('death_records', DeathRecordController::class);

        // Certificates - Office staff and system admin
        Route::resource('certificates', CertificateController::class);
    });
});

require __DIR__.'/auth.php';
