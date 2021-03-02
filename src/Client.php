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

use GuzzleHttp;
use Hyperf\Consul\KV;
use RuntimeException;
use Hyperf\Utils\Codec\Json;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;

class Client implements ClientInterface
{
    /**
     * @var array
     */
    public $fetchConfig;

    /**
     * @var null|GuzzleHttp\Client
     */
    private $client;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $servers;

    /**
     * @var array[]
     */
    private $cachedSecurityCredentials = [];

    public function __construct(ContainerInterface $container)
    {
        $clientFactory = $container->get(GuzzleClientFactory::class);
        $this->client = $clientFactory->create();
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function pull(): array
    {
        $client = $this->client;
        if (! $client instanceof GuzzleHttp\Client) {
            throw new RuntimeException('config center: Invalid http client.');
        }

        if(config('config_center.default') == 'consul')
        {
            return $this->consul();
        }else {
            return $this->config();
        }
    
        try {
        } catch (\Throwable $throwable) {
            $this->logger->error(sprintf('Config Center: %s[line:%d] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            return [];
        }
    }

    public function consul()
    {
        // ACM config
        $endpoint = $this->config->get('config_center.endpoint', 'localhost:9000');
        $namespace = $this->config->get('config_center.namespace', '');
        $version = $this->config->get('config_center.version', 'v1');

        $key    = $this->config->get('config_center.key', '');
        $secret = $this->config->get('config_center.secret', '');

        // Get config
        $kv = new KV(function () use ($client, $endpoint, $secret) {
            return $client->create([
                'base_uri' => $endpoint,
                'headers' => [
                    'X-Consul-Token' => $secret
                ],
            ]);
        });

        $response = $kv->get($namespace)->json();
        $content = $response[0]["Value"]?? null;
        
        if (! is_null($content)) {
            return Json::decode($content);
        }

        return [];
    }

    public function config()
    {
        // ACM config
        $endpoint = $this->config->get('config_center.endpoint', 'localhost:9000');
        $namespace = $this->config->get('config_center.namespace', '');
        $version = $this->config->get('config_center.version', 'v1');

        $key    = $this->config->get('config_center.key', '');
        $secret = $this->config->get('config_center.secret', '');

        // Get config
        $response = $client->get("{$endpoint}/{$namespace}", [
            'auth' => [$key, $secret],
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'version'       => $version
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Get config failed from Config center.');
        }
        $content = $response->getBody()->getContents();
        if (! $content) {
            return [];
        }

        return Json::decode($content);
    }
}
