<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Manager;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Str;
use Plexikon\Reporter\CommandPublisher;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Contracts\Publisher\Publisher;
use Plexikon\Reporter\EventPublisher;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Producer\AsyncMessageProducer;
use Plexikon\Reporter\Message\Producer\IlluminateProducer;
use Plexikon\Reporter\Message\Producer\SyncMessageProducer;
use Plexikon\Reporter\Publisher\Middleware\DefaultChainMessageDecoratorMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableCommandMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableEventMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableQuerySyncMiddleware;
use Plexikon\Reporter\Publisher\Router\MultipleHandlersRouter;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use Plexikon\Reporter\QueryPublisher;
use Plexikon\Reporter\Support\Message\ChainMessageDecorator;

class PublisherServiceManager extends AbstractPublisherManager
{
    public function createCommandPublisher(string $publisherName = 'default'): Publisher
    {
        return $this->createPublisher($publisherName, self::COMMAND_TYPE);
    }

    public function createQueryPublisher(string $publisherName = 'default'): Publisher
    {
        return $this->createPublisher($publisherName, self::QUERY_TYPE);
    }

    public function createEventPublisher(string $publisherName = 'default'): Publisher
    {
        return $this->createPublisher($publisherName, self::EVENT_TYPE);
    }

    protected function createDefaultCommandPublisherDriver(): Publisher
    {
        $middleware = $this->createDefaultPublisherMiddleware(self::COMMAND_TYPE);

        $publisher = new CommandPublisher(
            $this->container->get(MessageFactory::class),
            ...$this->resolveStuff($middleware)
        );

        $publisher->setPublisherName(CommandPublisher::class);

        return $publisher;
    }

    protected function createDefaultEventPublisherDriver(): Publisher
    {
        $middleware = $this->createDefaultPublisherMiddleware(self::EVENT_TYPE);

        $publisher = new EventPublisher(
            $this->container->get(MessageFactory::class),
            ...$this->resolveStuff($middleware)
        );

        $publisher->setPublisherName(EventPublisher::class);

        return $publisher;
    }

    protected function createDefaultQueryPublisherDriver(): Publisher
    {
        $middleware = $this->createDefaultPublisherMiddleware(self::QUERY_TYPE);

        $publisher = new QueryPublisher(
            $this->container->get(MessageFactory::class),
            ...$this->resolveStuff($middleware)
        );

        $publisher->setPublisherName(QueryPublisher::class);

        return $publisher;
    }


    private function createDefaultPublisherMiddleware(string $publisherType): array
    {
        $pubConfig = $this->fromReporter("publisher.$publisherType.default");

        $middleware = array_merge(
            $this->fromReporter('middleware') ?? [],
            $pubConfig['middleware'] ?? []
        );

        $middleware[] = $this->createDefaultMessageDecoratorMiddleware($pubConfig['message']['decorator'] ?? []);

        $routableMethod = 'createDefaultRoutable' . Str::studly($publisherType) . 'Middleware';

        if (method_exists($this, $routableMethod)) {
            return array_merge($middleware, [$this->$routableMethod($pubConfig)]);
        }

        throw new RuntimeException("Unable to determine router middleware with method $routableMethod");
    }

    protected function createDefaultRoutableCommandMiddleware(array $pubConfig): Middleware
    {
        $producer = $this->createMessageProducer($pubConfig['route_strategy']);

        $router = $this->createDefaultSingleHandlerRouter(
            $pubConfig['map'],
            $pubConfig['use_container'] ?? true,
            $pubConfig['handler_method'] ?? null
        );

        return new RoutableCommandMiddleware($router, $producer);
    }

    protected function createDefaultEventRoutableMiddleware(array $pubConfig): Middleware
    {
        $producer = $this->createMessageProducer($pubConfig['route_strategy']);

        $router = $this->createDefaultMultipleHandlersRouter(
            $pubConfig['map'],
            $pubConfig['use_container'] ?? true,
            $pubConfig['handler_method'] ?? null
        );

        return new RoutableEventMiddleware($router, $producer);
    }

    protected function createDefaultQueryRoutableMiddleware(array $pubConfig): Middleware
    {
        $router = $this->createDefaultSingleHandlerRouter(
            $pubConfig['map'],
            $pubConfig['use_container'] ?? true,
            $pubConfig['handler_method'] ?? null
        );

        return new RoutableQuerySyncMiddleware($router);
    }

    protected function createDefaultSingleHandlerRouter(array $map, bool $useContainer, ?string $callableMethod): SingleHandlerRouter
    {
        return new SingleHandlerRouter(
            $map,
            $this->container->get(MessageAlias::class),
            $useContainer ? $this->container : null,
            $callableMethod
        );
    }

    protected function createDefaultMultipleHandlersRouter(array $map, bool $useContainer, ?string $callableMethod): MultipleHandlersRouter
    {
        return new MultipleHandlersRouter(
            $map,
            $this->container->get(MessageAlias::class),
            $useContainer ? $this->container : null,
            $callableMethod
        );
    }

    protected function createMessageProducer(string $driver): MessageProducer
    {
        if ($customProducer = $this->customProducers[$driver] ?? null) {
            return $customProducer($this->container);
        }

        if ($producer = $this->producers[$driver] ?? null) {
            return $producer;
        }

        $config = $this->fromReporter("message.producer.$driver");

        if (!$config || empty($config)) {
            throw new RuntimeException("Invalid message producer driver $driver");
        }

        if ('sync' === $driver) {
            return $this->producers[$driver] = new SyncMessageProducer();
        }

        $connection = $config['connection'] ?? null;
        $queue = $config['queue'] ?? null;

        $illuminateProducer = new IlluminateProducer(
            $this->container->get(QueueingDispatcher::class),
            $this->container->make(MessageSerializer::class),
            $connection,
            $queue
        );

        if ('per_message' !== $driver && 'async_all' !== $driver) {
            throw new RuntimeException("Invalid message producer driver $driver");
        }

        $routeStrategy = 'per_message' === $driver
            ? MessageProducer::ROUTE_PER_MESSAGE : MessageProducer::ROUTE_ALL_ASYNC;

        return $this->producers[$driver] = new AsyncMessageProducer($illuminateProducer, $routeStrategy);
    }

    protected function createDefaultMessageDecoratorMiddleware(array $decorators): Middleware
    {
        $decorators = array_merge($this->fromReporter('message.decorators') ?? [], $decorators);

        return new DefaultChainMessageDecoratorMiddleware(
            new ChainMessageDecorator(...$this->resolveStuff($decorators))
        );
    }

    protected function resolveStuff(array $stuff): array
    {
        foreach ($stuff as &$item) {
            if (is_string($item)) {
                $item = $this->container->make($item);
            }
        }

        return $stuff;
    }
}
