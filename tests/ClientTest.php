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

namespace XtwoendTest\ConfigCenter;

use Hyperf\Config\Config;
use Xtwoend\ConfigCenter\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    public function testPull()
    {
        $container = Mockery::mock(ContainerInterface::class);
        // @TODO Add a test env.
        $configInstance = new Config([
            'config_center' => [
                'namespace' => '',
                'data_id' => 'hyperf',
                'key' => '',
                'secret' => '',
            ],
        ]);
        $configInstance->set('config_center.test-key', 'pre-value');
        $container->shouldReceive('get')->with(ClientFactory::class)->andReturn(new ClientFactory($container));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($configInstance);
        ApplicationContext::setContainer($container);
        $client = new Client($container);
        $fetchConfig = $client->pull();
        $this->assertSame('after-value', $fetchConfig['config_center.test-key']);
    }
}
