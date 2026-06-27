<?php

return [

    'driver' => env('BACKUP_DRIVER', 'local'),

    'disk' => env('BACKUP_DISK', 'local'),

    'path' => 'backups',

    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    'mysql_dump' => env('MYSQLDUMP_PATH', 'mysqldump'),

    'mysql_path' => env('MYSQL_PATH', 'mysql'),

    'include_paths' => [
        public_path('image'),
        public_path('produtos'),
        public_path('devolucoes'),
        public_path('uploads'),
    ],

];