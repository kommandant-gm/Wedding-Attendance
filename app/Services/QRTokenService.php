<?php

namespace App\Services;

use App\Models\Guest;
use Exception;

class QRTokenService
{
    public function generateToken(Guest $guest): string
    {
        $payload = [
            'guest_id' => $guest->id,
            'exp' => now()->addMonths(6)->timestamp,
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha256', $json, config('app.key'));

        return base64_encode($json . '.' . $signature);
    }

    public function validateToken(string $token): int
    {
        try {
            $decoded = base64_decode($token);

            if (!str_contains($decoded, '.')) {
                throw new Exception('Invalid token format');
            }

            [$json, $signature] = explode('.', $decoded, 2);
            $expectedSignature = hash_hmac('sha256', $json, config('app.key'));

            if (!hash_equals($expectedSignature, $signature)) {
                throw new Exception('Invalid token signature');
            }

            $payload = json_decode($json, true);

            if (!isset($payload['exp']) || $payload['exp'] < now()->timestamp) {
                throw new Exception('Token has expired');
            }

            if (!isset($payload['guest_id'])) {
                throw new Exception('Invalid token payload');
            }

            return $payload['guest_id'];
        } catch (Exception $e) {
            throw new Exception('Invalid QR code: ' . $e->getMessage());
        }
    }
}
