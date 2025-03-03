<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [
        'error_log' => [
            'driver' => 'single',
            'path' => storage_path('logs/error.log'),
            'level' => 'error',
        ],
        'info_log' => [
            'driver' => 'single',
            'path' => storage_path('logs/info.log'),
            'level' => 'info',
        ],
        'success_log' => [
            'driver' => 'single',
            'path' => storage_path('logs/success.log'),
            'level' => 'info',
        ],
        'debug_log' => [
            'driver' => 'single',
            'path' => storage_path('logs/debug.log'),
            'level' => 'debug',
        ],
        'warning_log' => [
            'driver' => 'single',
            'path' => storage_path('logs/warning.log'),
            'level' => 'warning',
        ],

        'muvi-error' => [
            'driver' => 'single',
            'path' => storage_path('logs/muvi/muvi-error.log'),
            'level' => 'error',
        ],
        'muvi-success' => [
            'driver' => 'single',
            'path' => storage_path('logs/muvi/muvi-success.log'),
            'level' => 'debug',
        ],
        'muvi-info' => [
            'driver' => 'single',
            'path' => storage_path('logs/muvi/muvi-info.log'),
            'level' => 'info',
        ],
        'muvi-warining' => [
            'driver' => 'single',
            'path' => storage_path('logs/muvi/muvi-warining.log'),
            'level' => 'warning',
        ],

        'inventory-error' => [
            'driver' => 'single',
            'path' => storage_path('logs/inventory/inventory-error.log'),
            'level' => 'error',
        ],
        'inventory-success' => [
            'driver' => 'single',
            'path' => storage_path('logs/inventory/inventory-success.log'),
            'level' => 'debug',
        ],
        'inventory-info' => [
            'driver' => 'single',
            'path' => storage_path('logs/inventory/inventory-info.log'),
            'level' => 'info',
        ],
        'inventory-warining' => [
            'driver' => 'single',
            'path' => storage_path('logs/inventory/inventory-warining.log'),
            'level' => 'warning',
        ],

        'sellouter-error' => [
            'driver' => 'single',
            'path' => storage_path('logs/sellouter/sellouter-error.log'),
            'level' => 'error',
        ],
        'sellouter-success' => [
            'driver' => 'single',
            'path' => storage_path('logs/sellouter/sellouter-success.log'),
            'level' => 'debug',
        ],
        'sellouter-info' => [
            'driver' => 'single',
            'path' => storage_path('logs/sellouter/sellouter-info.log'),
            'level' => 'info',
        ],
        'sellouter-warining' => [
            'driver' => 'single',
            'path' => storage_path('logs/sellouter/sellouter-warining.log'),
            'level' => 'warning',
        ],

        'csv-error' => [
            'driver' => 'single',
            'path' => storage_path('logs/csv/csv-error.log'),
            'level' => 'error',
        ],
        'csv-success' => [
            'driver' => 'single',
            'path' => storage_path('logs/csv/csv-success.log'),
            'level' => 'debug',
        ],
        'csv-info' => [
            'driver' => 'single',
            'path' => storage_path('logs/csv/csv-info.log'),
            'level' => 'info',
        ],
        'csv-warining' => [
            'driver' => 'single',
            'path' => storage_path('logs/csv/csv-warining.log'),
            'level' => 'warning',
        ]
    ],
];
