<?php
return [
    'connections' => [
        'pgsql' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE'),
            'charset'  => 'utf8'
        ],
        'testing' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_TEST_DATABASE'),
            'charset'  => 'utf8'
        ],
        'um' => [
            'driver'   => env('DB_CONNECTION_UM'),
            'host'     => env('DB_HOST_UM'),
            'port'     => env('DB_PORT_UM'),
            'username' => env('DB_USERNAME_UM'),
            'password' => env('DB_PASSWORD_UM'),
            'database' => env('DB_DATABASE_UM'),
            'charset'  => 'utf8'
        ],
        'time' => [
            'driver'   => env('DB_CONNECTION_TIME'),
            'host'     => env('DB_HOST_TIME'),
            'port'     => env('DB_PORT_TIME'),
            'username' => env('DB_USERNAME_TIME'),
            'password' => env('DB_PASSWORD_TIME'),
            'database' => env('DB_DATABASE_TIME'),
            'charset'  => 'utf8'
        ],
        'payroll' => [
            'driver'   => env('DB_CONNECTION_PAYROLL'),
            'host'     => env('DB_HOST_PAYROLL'),
            'port'     => env('DB_PORT_PAYROLL'),
            'username' => env('DB_USERNAME_PAYROLL'),
            'password' => env('DB_PASSWORD_PAYROLL'),
            'database' => env('DB_DATABASE_PAYROLL'),
            'charset'  => 'utf8'
        ],
        'log' => [
            'driver'   => env('DB_CONNECTION_LOG'),
            'host'     => env('DB_HOST_LOG'),
            'port'     => env('DB_PORT_LOG'),
            'username' => env('DB_USERNAME_LOG'),
            'password' => env('DB_PASSWORD_LOG'),
            'database' => env('DB_DATABASE_LOG'),
            'charset'  => 'utf8'
        ],        
        'appraisal' => [
            'driver'   => env('DB_CONNECTION_APPRAISAL'),
            'host'     => env('DB_HOST_APPRAISAL'),
            'port'     => env('DB_PORT_APPRAISAL'),
            'username' => env('DB_USERNAME_APPRAISAL'),
            'password' => env('DB_PASSWORD_APPRAISAL'),
            'database' => env('DB_DATABASE_APPRAISAL'),
            'charset'  => 'utf8'
        ],
        'talent' => [
            'driver'   => env('DB_CONNECTION_TALENT'),
            'host'     => env('DB_HOST_TALENT'),
            'port'     => env('DB_PORT_TALENT'),
            'username' => env('DB_USERNAME_TALENT'),
            'password' => env('DB_PASSWORD_TALENT'),
            'database' => env('DB_DATABASE_TALENT'),
            'charset'  => 'utf8'
        ],
        'travel' => [
            'driver'   => env('DB_CONNECTION_TRAVEL'),
            'host'     => env('DB_HOST_TRAVEL'),
            'port'     => env('DB_PORT_TRAVEL'),
            'username' => env('DB_USERNAME_TRAVEL'),
            'password' => env('DB_PASSWORD_TRAVEL'),
            'database' => env('DB_DATABASE_TRAVEL'),
            'charset'  => 'utf8'
        ]
    ],
    'migrations' => 'migrations',
    'default' => env('APP_ENV') === 'testing' ? 'testing' : env('DB_CONNECTION')
];
