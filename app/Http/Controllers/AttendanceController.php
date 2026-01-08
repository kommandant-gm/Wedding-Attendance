<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Guest;
use App\Models\ScanLog;
use App\Services\QRTokenService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

        $tokenHash = hash('sha256', $validated['token']);

        try {
            $guestId = $this->qrTokenService->validateToken($validated['token']);
            $guest = Guest::with('attendance')->findOrFail($guestId);

            if ($guest->attendance) {
                $this->recordScanLog($request, [
                    'guest_id' => $guest->id,
                    'status' => 'already_checked_in',
                    'http_status' => 409,
                    'token_hash' => $tokenHash,
                ]);

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

            $this->recordScanLog($request, [
                'guest_id' => $guest->id,
                'status' => 'checked_in',
                'http_status' => 200,
                'token_hash' => $tokenHash,
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
            $message = $e->getMessage();

            if (str_contains($message, 'expired')) {
                $this->recordScanLog($request, [
                    'status' => 'expired',
                    'http_status' => 400,
                    'token_hash' => $tokenHash,
                    'error_message' => $message,
                    'exception_class' => $e::class,
                ]);

                return response()->json([
                    'error' => 'QR code has expired',
                ], 400);
            }

            if (str_contains($message, 'Invalid')) {
                $this->recordScanLog($request, [
                    'status' => 'invalid',
                    'http_status' => 400,
                    'token_hash' => $tokenHash,
                    'error_message' => $message,
                    'exception_class' => $e::class,
                ]);

                return response()->json([
                    'error' => 'Invalid QR code',
                ], 400);
            }

            if ($e instanceof ModelNotFoundException) {
                $this->recordScanLog($request, [
                    'status' => 'not_found',
                    'http_status' => 404,
                    'token_hash' => $tokenHash,
                    'error_message' => $message,
                    'exception_class' => $e::class,
                ]);

                return response()->json([
                    'error' => 'Guest not found',
                ], 404);
            }

            $this->recordScanLog($request, [
                'status' => 'error',
                'http_status' => 500,
                'token_hash' => $tokenHash,
                'error_message' => $message,
                'exception_class' => $e::class,
            ]);

            return response()->json([
                'error' => 'Server error',
            ], 500);
        } catch (\Throwable $e) {
            $this->recordScanLog($request, [
                'status' => 'error',
                'http_status' => 500,
                'token_hash' => $tokenHash,
                'error_message' => $e->getMessage(),
                'exception_class' => $e::class,
            ]);

            return response()->json([
                'error' => 'Server error',
            ], 500);
        }
    }

    private function recordScanLog(Request $request, array $data): void
    {
        try {
            ScanLog::create([
                'guest_id' => $data['guest_id'] ?? null,
                'status' => $data['status'] ?? 'error',
                'http_status' => $data['http_status'] ?? null,
                'token_hash' => $data['token_hash'] ?? null,
                'exception_class' => $data['exception_class'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('Failed to record scan log', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
