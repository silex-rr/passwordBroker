<?php

namespace Identity\Application\Services;

use Identity\Domain\User\Models\Attributes\Fingerprint;

readonly class FingerprintService
{
    public function getFingerprintBack(): array
    {
        return array_filter(
            $_SERVER,
            fn($key) => (str_starts_with($key, "REMOTE_") || str_starts_with($key, "HTTP_")) && $key !== 'HTTP_COOKIE',
            ARRAY_FILTER_USE_KEY
        );
    }

    public function makeFingerprint(array $fingerprintFront = []): Fingerprint
    {
        return new Fingerprint(
            json: null,
            data: [
                'front' => $fingerprintFront,
                'back' => $this->getFingerprintBack(),
            ]);
    }
}
