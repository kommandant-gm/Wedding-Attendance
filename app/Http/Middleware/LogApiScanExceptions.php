<?php

namespace App\Http\Middleware;

use App\Models\ScanLog;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class LogApiScanExceptions
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            $this->recordScanLog($request, $e);
            throw $e;
        }
    }

    private function recordScanLog(Request $request, Throwable $e): void
    {
        if (! $request->is('api/attendance/check-in')) {
            return;
        }

        $token = $request->input('token');
        $tokenHash = is_string($token) && $token !== '' ? hash('sha256', $token) : null;

        try {
            ScanLog::create([
                'status' => 'error',
                'http_status' => 500,
                'token_hash' => $tokenHash,
                'exception_class' => $e::class,
                'error_message' => $e->getMessage(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (Throwable $logException) {
            logger()->warning('Failed to record scan log (middleware)', [
                'error' => $logException->getMessage(),
            ]);
        }
    }
}
