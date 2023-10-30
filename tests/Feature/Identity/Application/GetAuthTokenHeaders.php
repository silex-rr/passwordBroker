<?php

namespace Tests\Feature\Identity\Application;

trait GetAuthTokenHeaders
{
    /**
     * @param string $token
     * @return string[]
     */
    public function getAuthTokenHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }
}
