<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => env('CONFIG_CENTER_DEFAULT', 'config-center'),
    'enable' => env('CONFIG_CENTER_ENABLE', false),
    'use_standalone_process' => false,
    'interval' => 5,
    'endpoint' => env('CONFIG_CENTER_HOST', 'localhost:8000'),
    'namespace' => env('CONFIG_CENTER_NAMESPACE', 'gateway'),
    'api_key'   => env('CONFIG_CENTER_API_KEY', null),
    'client'       => env('CONFIG_CENTER_CLIENT', null),
    'secret'    => env('CONFIG_CENTER_SECRET', null),
];
