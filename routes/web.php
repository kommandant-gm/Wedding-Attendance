<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GuestImportController;
use App\Models\Attendance;
use App\Models\Guest;
use App\Models\ScanLog;
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
            'guestCount' => Guest::count(),
            'resetResult' => $request->session()->get('attendance_reset'),
            'qrResetResult' => $request->session()->get('qr_regenerated'),
            'diagnosticsResult' => $request->session()->get('diagnostics_result'),
            'qrTestResult' => $request->session()->get('qr_test_result'),
            'scanLogs' => ScanLog::query()
                ->with('guest:id,name,table_name,hall')
                ->latest()
                ->limit(50)
                ->get(),
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

    Route::post('/settings/qr/regenerate', function (Request $request) {
        if (! $request->user() || $request->user()->username !== 'amir') {
            abort(403);
        }

        $updated = 0;
        Guest::query()->orderBy('id')->chunk(200, function ($guests) use (&$updated) {
            foreach ($guests as $guest) {
                $guest->forceFill(['qr_secret' => Str::random(32)])->save();
                $updated++;
            }
        });

        return back()->with('qr_regenerated', [
            'updated' => $updated,
        ]);
    })->name('settings.qr.regenerate');

    Route::post('/settings/qr/test', function (Request $request) {
        if (! $request->user() || $request->user()->username !== 'amir') {
            abort(403);
        }

        $validated = $request->validate([
            'token' => 'nullable|string',
            'guest_id' => 'nullable|integer|exists:guests,id',
        ]);

        $token = isset($validated['token']) ? trim($validated['token']) : '';
        $guestId = $validated['guest_id'] ?? null;
        $qrTokenService = app(QRTokenService::class);

        if ($token !== '') {
            try {
                $validatedGuestId = $qrTokenService->validateToken($token);
                $guest = Guest::with('attendance')->findOrFail($validatedGuestId);

                return back()->with('qr_test_result', [
                    'ok' => true,
                    'mode' => 'token',
                    'token' => $token,
                    'guest' => [
                        'id' => $guest->id,
                        'name' => $guest->name,
                        'table' => $guest->table_name,
                        'hall' => $guest->hall,
                        'checked_in_at' => optional($guest->attendance)->checked_in_at,
                    ],
                ]);
            } catch (\Throwable $e) {
                return back()->with('qr_test_result', [
                    'ok' => false,
                    'mode' => 'token',
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($guestId) {
            try {
                $guest = Guest::with('attendance')->findOrFail($guestId);
                $generatedToken = $qrTokenService->generateToken($guest);

                $validatedGuestId = $qrTokenService->validateToken($generatedToken);
                if ($validatedGuestId !== $guest->id) {
                    throw new Exception('Token validation returned a different guest ID');
                }

                return back()->with('qr_test_result', [
                    'ok' => true,
                    'mode' => 'generate',
                    'token' => $generatedToken,
                    'guest' => [
                        'id' => $guest->id,
                        'name' => $guest->name,
                        'table' => $guest->table_name,
                        'hall' => $guest->hall,
                        'checked_in_at' => optional($guest->attendance)->checked_in_at,
                    ],
                ]);
            } catch (\Throwable $e) {
                return back()->with('qr_test_result', [
                    'ok' => false,
                    'mode' => 'generate',
                    'error' => $e->getMessage(),
                    'guest_id' => $guestId,
                ]);
            }
        }

        return back()->with('qr_test_result', [
            'ok' => false,
            'mode' => 'none',
            'error' => 'Provide a token or guest ID to test.',
        ]);
    })->name('settings.qr.test');

    Route::post('/settings/diagnostics', function (Request $request) {
        if (! $request->user() || $request->user()->username !== 'amir') {
            abort(403);
        }

        $connection = config('database.default');
        $details = [
            'app_env' => config('app.env'),
            'app_url' => config('app.url'),
            'db_connection' => $connection,
            'db_database' => config("database.connections.$connection.database"),
            'db_host' => config("database.connections.$connection.host"),
        ];

        try {
            $scanLog = ScanLog::create([
                'status' => 'diagnostic',
                'http_status' => 200,
                'error_message' => 'Diagnostics test log entry',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $details['scan_log_id'] = $scanLog->id;
            $details['scan_logs_count'] = ScanLog::count();

            return back()->with('diagnostics_result', [
                'ok' => true,
                'details' => $details,
            ]);
        } catch (\Throwable $e) {
            return back()->with('diagnostics_result', [
                'ok' => false,
                'error' => $e->getMessage(),
                'details' => $details,
            ]);
        }
    })->name('settings.diagnostics');
    
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    });
});

Route::get('/', function () {
    return redirect('/login');
});
