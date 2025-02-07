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

        'muvi' => [
            'driver' => 'single',
            'path' => storage_path('logs/muvi.log'),
            'level' => 'debug',
        ],
        'inventory' => [
            'driver' => 'single',
            'path' => storage_path('logs/inventory.log'),
            'level' => 'debug',
        ],
        'sellouter' => [
            'driver' => 'single',
            'path' => storage_path('logs/sellouter.log'),
            'level' => 'debug',
        ],
    ],
];
