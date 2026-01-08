<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GuestImportController;
use App\Models\Guest;
use App\Services\QRTokenService;

Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

Route::post('/login', function () {
    // Simple login logic
    request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    if (Auth::attempt(request()->only('email', 'password'))) {
        request()->session()->regenerate();
        return redirect('/dashboard');
    }
    
    return back()->withErrors(['email' => 'Invalid credentials']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    });
    
    Route::get('/scan', function () {
        return Inertia::render('GuestScan');
    });

    Route::get('/guests/import', [GuestImportController::class, 'create'])->name('guests.import');
    Route::post('/guests/import', [GuestImportController::class, 'store'])->name('guests.import.store');

    Route::get('/attendance', function () {
        $qrTokenService = app(QRTokenService::class);

        return Inertia::render('Attendance', [
            'guests' => Guest::query()
                ->with('attendance')
                ->orderBy('name')
                ->get()
                ->map(function ($guest) use ($qrTokenService) {
                    $guest->qr_token = $qrTokenService->generateToken($guest);
                    return $guest;
                }),
        ]);
    });
    
    Route::get('/reports', function () {
        return Inertia::render('Reports');
    });
    
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    });
});

Route::get('/', function () {
    return redirect('/login');
});





