<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Manager;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\Contracts\Publisher\Publisher;
use Plexikon\Reporter\Exception\RuntimeException;

abstract class AbstractPublisherManager
{
    const COMMAND_TYPE = Messaging::COMMAND;
    const EVENT_TYPE = Messaging::EVENT;
    const QUERY_TYPE = Messaging::QUERY;

    protected array $publishers = [];
    protected array $customPublishers = [];
    protected array $customRouters = [];
    protected array $producers = [];
    protected array $customProducers = [];
    protected array $config = [];
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function createPublisher(string $driver, string $publisherType): Publisher
    {
        $driverKey = $this->determinePublisherKey($driver, $publisherType);

        if ($publisher = $this->publishers[$driverKey] ?? null) {
            return $publisher;
        }

        // todo separate publishers command query event

        $customPublisher = $this->customPublishers[$driverKey] ?? null;

        if($customPublisher){
            return $customPublisher($this->container);
        }

        $method = 'create' .Str::studly($driver . $publisherType).'PublisherDriver';

        if (method_exists($this, $method)) {
            return $this->publishers[$driverKey] = $this->$method();
        }

        throw new RuntimeException("Unable to resolve publisher with driver $driver");
    }

    protected function fromReporter(string $key)
    {
        if (!$this->config) {
            $this->config = $this->container->get(Repository::class)->get('reporter');
        }

        return Arr::get($this->config, $key);
    }

    public function customPublisher(string $driver, callable $publisher): void
    {
        // probably add type
        $this->customPublishers[$driver] = $publisher;
    }

    public function customProducer(string $driver, callable $messageProducer): void
    {
        $this->customProducers[$driver] = $messageProducer;
    }

    public function determinePublisherKey(string $driver, string $publisherType): string
    {
        return $publisherType.'-'.$driver;
    }
}
