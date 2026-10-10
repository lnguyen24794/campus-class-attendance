<?php

namespace App\Services;

class PublicUrlService
{
    /**
     * Resolve the public base URL used inside QR codes (tunnel / APP_URL).
     */
    public function baseUrl(): string
    {
        $public = trim((string) config('app.public_url', ''));

        if ($public !== '') {
            return rtrim($public, '/');
        }

        return rtrim((string) config('app.url'), '/');
    }

    /**
     * Build the public check-in URL encoded into the QR image.
     */
    public function checkInUrl(string $qrToken): string
    {
        return $this->baseUrl().'/check-in/'.$qrToken;
    }
}
