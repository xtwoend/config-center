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
namespace Xtwoend\ConfigCenter\Listener;

use Xtwoend\ConfigCenter\ClientInterface;
use Xtwoend\ConfigCenter\PipeMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Process\Event\PipeMessage as UserProcessPipeMessage;

class OnPipeMessageListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger, ClientInterface $client)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnPipeMessage::class,
            UserProcessPipeMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        if (! $this->config->get('config_center.enable', false)) {
            return;
        }
        if (property_exists($event, 'data') && $event->data instanceof PipeMessage) {
            foreach ($event->data->data ?? [] as $key => $value) {
                $this->mergeConfig($key, $value);
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }
    }

    protected function mergeConfig(string $key, $configValues)
    {
        $config = $this->config->get($key);

        if (! is_array($config)) {
            $this->config->set($key, $configValues);
            return;
        }
        
        $this->config->set($key, array_merge_recursive($config, $configValues));
    }
}
