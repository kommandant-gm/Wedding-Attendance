<?php

namespace App\Services;

use App\Models\Guest;
use Exception;
use Illuminate\Support\Str;

class QRTokenService
{
    private function getSigningKey(): string
    {
        $key = config('app.key');

        if (!is_string($key) || $key === '') {
            throw new Exception('Missing app key');
        }

        return $key;
    }

    public function generateToken(Guest $guest): string
    {
        if (empty($guest->qr_secret)) {
            $guest->forceFill(['qr_secret' => Str::random(32)])->save();
        }

        $payload = [
            'guest_id' => $guest->id,
            'exp' => now()->addMonths(6)->timestamp,
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha256', $json, $this->getSigningKey() . $guest->qr_secret);

        return base64_encode($json . '.' . $signature);
    }

    public function validateToken(string $token): int
    {
        try {
            $decoded = base64_decode(trim($token), true);

            if ($decoded === false) {
                throw new Exception('Invalid token format');
            }

            if (!str_contains($decoded, '.')) {
                throw new Exception('Invalid token format');
            }

            [$json, $signature] = explode('.', $decoded, 2);
            $payload = json_decode($json, true);
            if (!is_array($payload)) {
                throw new Exception('Invalid token payload');
            }

            if (!isset($payload['exp']) || $payload['exp'] < now()->timestamp) {
                throw new Exception('Token has expired');
            }

            if (!isset($payload['guest_id'])) {
                throw new Exception('Invalid token payload');
            }

            $guest = Guest::find($payload['guest_id']);

            if (!$guest || empty($guest->qr_secret)) {
                throw new Exception('Invalid token payload');
            }

            $expectedSignature = hash_hmac('sha256', $json, $this->getSigningKey() . $guest->qr_secret);

            if (!hash_equals($expectedSignature, $signature)) {
                throw new Exception('Invalid token signature');
            }

            return $guest->id;
        } catch (\Throwable $e) {
            throw new Exception('Invalid QR code: ' . $e->getMessage());
        }
    }
}
