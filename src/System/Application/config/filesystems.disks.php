<?php
return [
    'system_backup' => [
        'driver' => 'local',
        'root' => storage_path(env('FILESYSTEM_DISK_TEST', 'app')
            . DIRECTORY_SEPARATOR . 'backups'
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
