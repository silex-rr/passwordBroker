<?php
return [
    'keepass_import' => [
        'driver' => 'local',
        'root' => storage_path(env('FILESYSTEM_DISK_TEST', 'app')
            . DIRECTORY_SEPARATOR . 'src'
            . DIRECTORY_SEPARATOR . 'PasswordBroker'
            . DIRECTORY_SEPARATOR . 'Infrastructure'
            . DIRECTORY_SEPARATOR . 'KeepassImport'
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
