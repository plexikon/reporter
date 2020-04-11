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

class ReporterManager
{
    protected array $publishers = [];
    protected array $customPublishers = [];
    protected array $producers = [];
    protected array $customProducers = [];
    protected array $config;
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->config = $container->get(Repository::class)->get('reporter');
    }

    public function createPublisher(string $name, string $type): Publisher
    {
        $this->assertPublisherTypeExists($type);

        $key = $this->determinePublisherKey($name, $type);

        if ($publisher = $this->publishers[$key] ?? null) {
            return $publisher;
        }

        if ($customPublisher = $this->customPublishers[$key] ?? null) {
            return $customPublisher;
        }

        $method = 'create' . Str::studly($name . $type) . 'PublisherDriver';

        if (method_exists($this, $method)) {
            return $this->publishers[$key] = $this->$method();
        }

        throw new RuntimeException("Unable to resolve publisher with driver $name and type $type");
    }

    public function commandPublisher(?string $name = null): Publisher
    {
        return $this->createPublisher($name ?? 'default', Messaging::COMMAND);
    }

    public function queryPublisher(?string $name = null): Publisher
    {
        return $this->createPublisher($name ?? 'default', Messaging::QUERY);
    }

    public function eventPublisher(?string $name = null): Publisher
    {
        return $this->createPublisher($publisherName ?? 'default', Messaging::EVENT);
    }

    public function createCustomPublisher(string $name, string $type, callable $publisher): void
    {
        $key = $this->determinePublisherKey($name, $type);

        $this->customPublishers[$key] = $publisher;
    }

    public function createCustomProducer(string $name, callable $messageProducer): void
    {
        $this->customProducers[$name] = $messageProducer;
    }

    public function hasCustomPublisher(string $name, string $type): bool
    {
        $key = $this->determinePublisherKey($name, $type);

        return isset($this->customPublishers[$key]);
    }

    protected function determinePublisherKey(string $driver, string $type): string
    {
        return $type . '-' . $driver;
    }

    protected function fromReporter(string $key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }

    protected function assertPublisherTypeExists(string $type): void
    {
        $publisherTypes = [Messaging::COMMAND, Messaging::QUERY, Messaging::EVENT];

        if (!in_array($type, $publisherTypes)) {
            throw new RuntimeException("Publisher type $type does not exists");
        }
    }
}
