<?php

use Identity\Domain\User\Models\User;

return [
    'users' => [
        'driver' => 'eloquent',
        'model' => User::class,
    ],
];
