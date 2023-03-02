<?php
return [
    'identity_keys' => [
        'driver' => 'local',
        'root' => storage_path(env('FILESYSTEM_DISK_TEST', 'app')
            . DIRECTORY_SEPARATOR . 'src'
            . DIRECTORY_SEPARATOR . 'Identity'
            . DIRECTORY_SEPARATOR . 'Infrastructure'
            . DIRECTORY_SEPARATOR . 'Keys'
        ),
        'throw' => false,
        'permissions' => [
            'file' => [
                'public' => 0664,
                'private' => 0600,
            ],
            'dir' => [
                'public' => 0775,
                'private' => 0700,
            ],
        ],
    ]
];
