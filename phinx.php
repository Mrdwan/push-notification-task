<?php

use app\configs\Database;

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/app/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/app/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_environment' => 'development',
        'development' => [
            'adapter' => Database::ADAPTER,
            'host' => Database::HOST,
            'name' => Database::NAME,
            'port' => Database::PORT,
            'user' => Database::USER_NAME,
            'pass' => Database::PASSWORD,
            'charset' => Database::CHARSET,
        ],
    ],
    'version_order' => 'creation'
];
