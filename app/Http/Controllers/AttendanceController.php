<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Guest;
use App\Services\QRTokenService;
use Exception;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected QRTokenService $qrTokenService
    ) {}

    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $guestId = $this->qrTokenService->validateToken($validated['token']);
            $guest = Guest::with('attendance')->findOrFail($guestId);

            if ($guest->attendance) {
                return response()->json([
                    'already_checked_in' => true,
                    'message' => 'Guest has already checked in',
                    'guest' => [
                        'id' => $guest->id,
                        'name' => $guest->name,
                        'table' => $guest->table_name,
                        'hall' => $guest->hall,
                        'phone' => $guest->phone,
                        'checked_in_at' => $guest->attendance->checked_in_at,
                    ],
                ], 409);
            }

            $attendance = Attendance::create([
                'guest_id' => $guest->id,
                'checked_in_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful',
                'guest' => [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'table' => $guest->table_name,
                    'hall' => $guest->hall,
                    'phone' => $guest->phone,
                    'checked_in_at' => $attendance->checked_in_at,
                ],
            ], 200);

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'expired')) {
                return response()->json([
                    'error' => 'QR code has expired',
                ], 400);
            }

            if (str_contains($e->getMessage(), 'Invalid')) {
                return response()->json([
                    'error' => 'Invalid QR code',
                ], 400);
            }

            return response()->json([
                'error' => 'Guest not found',
            ], 404);
        }
    }
}
