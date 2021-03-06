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

namespace Xtwoend\ConfigCenter;

use Xtwoend\ConfigCenter\Listener\BootProcessListener;
use Xtwoend\ConfigCenter\Listener\OnPipeMessageListener;
use Xtwoend\ConfigCenter\Process\ConfigFetcherProcess;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ClientInterface::class => Client::class,
            ],
            'processes' => [
                ConfigFetcherProcess::class,
            ],
            'listeners' => [
                BootProcessListener::class,
                OnPipeMessageListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for config center.',
                    'source' => __DIR__ . '/../publish/config_center.php',
                    'destination' => BASE_PATH . '/config/config_center.php',
                ],
            ],
        ];
    }
}
