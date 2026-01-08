<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GuestImportController;
use App\Models\Attendance;
use App\Models\Guest;
use App\Services\QRTokenService;

Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

Route::post('/login', function () {
    // Simple login logic
    request()->validate([
        'username' => 'required|string',
        'password' => 'required',
    ]);
    
    if (Auth::attempt(request()->only('username', 'password'), request()->boolean('remember'))) {
        request()->session()->regenerate();
        return redirect('/dashboard');
    }
    
    return back()->withErrors(['username' => 'Invalid credentials']);
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

    Route::get('/settings', function (Request $request) {
        if (! $request->user() || $request->user()->username !== 'amir') {
            abort(403);
        }

        return Inertia::render('Settings', [
            'checkedInCount' => Attendance::count(),
            'resetResult' => $request->session()->get('attendance_reset'),
        ]);
    })->name('settings');

    Route::post('/settings/attendance/reset', function (Request $request) {
        if (! $request->user() || $request->user()->username !== 'amir') {
            abort(403);
        }

        $cleared = Attendance::count();
        Attendance::query()->delete();

        return back()->with('attendance_reset', [
            'cleared' => $cleared,
        ]);
    })->name('settings.attendance.reset');
    
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    });
});

Route::get('/', function () {
    return redirect('/login');
});



