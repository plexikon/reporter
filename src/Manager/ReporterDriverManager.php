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
use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Contracts\Publisher\NamedPublisher;
use Plexikon\Reporter\Contracts\Publisher\Publisher;
use Plexikon\Reporter\EventPublisher;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Producer\AsyncMessageProducer;
use Plexikon\Reporter\Message\Producer\IlluminateProducer;
use Plexikon\Reporter\Message\Producer\SyncMessageProducer;
use Plexikon\Reporter\Publisher\Middleware\ChainMessageDecoratorMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableCommandMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableEventMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableQuerySyncMiddleware;
use Plexikon\Reporter\Publisher\Router\MultipleHandlersRouter;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use Plexikon\Reporter\QueryPublisher;
use Plexikon\Reporter\Support\Message\ChainMessageDecorator;

class ReporterDriverManager extends ReporterManager
{
    protected function createPublisherTypeDriver(string $className, string $type): Publisher
    {
        $middleware = $this->resolvePublisherMiddleware($type);

        $publisher = new $className(
            $this->container->get(MessageFactory::class),
            ...$this->resolveServices($middleware)
        );

        if ($publisher instanceof NamedPublisher) {
            $publisher->setPublisherName(CommandPublisher::class);
        }

        return $publisher;
    }

    protected function createDefaultCommandPublisherDriver(): Publisher
    {
        return $this->createPublisherTypeDriver(CommandPublisher::class, Messaging::COMMAND);
    }

    protected function createDefaultEventPublisherDriver(): Publisher
    {
        return $this->createPublisherTypeDriver(EventPublisher::class, Messaging::EVENT);
    }

    protected function createDefaultQueryPublisherDriver(): Publisher
    {
        return $this->createPublisherTypeDriver(QueryPublisher::class, Messaging::QUERY);
    }

    protected function resolvePublisherMiddleware(string $type): array
    {
        $pubConfig = $this->fromReporter("publisher.$type.default");

        $middleware = [];
        $middleware[] = $this->resolveMessageDecoratorMiddleware($pubConfig['message']['decorator'] ?? []);

        $middleware = array_merge($middleware, $this->fromReporter('middleware', []), $pubConfig['middleware'] ?? []);

        $routableMethod = 'createDefaultRoutable' . Str::studly($type) . 'Middleware';

        if (method_exists($this, $routableMethod)) {
            return array_merge($middleware, [$this->$routableMethod($pubConfig)]);
        }

        throw new RuntimeException("Unable to determine router middleware with method $routableMethod");
    }

    protected function createDefaultRoutableCommandMiddleware(array $pubConfig): Middleware
    {
        $producer = $this->createMessageProducer($pubConfig['route_strategy'] ?? null);

        $router = $this->createDefaultSingleHandlerRouter(
            $pubConfig['map'],
            $pubConfig['use_container'] ?? true,
            $pubConfig['handler_method'] ?? null
        );

        return new RoutableCommandMiddleware($router, $producer);
    }

    protected function createDefaultRoutableEventMiddleware(array $pubConfig): Middleware
    {
        $producer = $this->createMessageProducer($pubConfig['route_strategy'] ?? null);

        $router = $this->createDefaultMultipleHandlersRouter(
            $pubConfig['map'],
            $pubConfig['use_container'] ?? true,
            $pubConfig['handler_method'] ?? null
        );

        return new RoutableEventMiddleware($router, $producer);
    }

    protected function createDefaultRoutableQueryMiddleware(array $pubConfig): Middleware
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

    protected function createMessageProducer(?string $driver): MessageProducer
    {
        if (null === $driver) {
            $driver = $this->fromReporter("message.producer.default");
        }

        if ($customProducer = $this->customProducers[$driver] ?? null) {
            return $customProducer($this->container);
        }

        if ($producer = $this->producers[$driver] ?? null) {
            return $producer;
        }

        if ('sync' === $driver) {
            return $this->producers[$driver] = new SyncMessageProducer();
        }

        if ('per_message' !== $driver && 'async_all' !== $driver) {
            throw new RuntimeException("Invalid message producer driver $driver");
        }

        $config = $this->fromReporter("message.producer.$driver");

        if (!$config || empty($config)) {
            throw new RuntimeException("Invalid message producer driver $driver");
        }

        $illuminateProducer = new IlluminateProducer(
            $this->container->get(QueueingDispatcher::class),
            $this->container->get(MessageSerializer::class),
            $config['connection'] ?? null,
            $config['queue'] ?? null
        );

        $routeStrategy = 'per_message' === $driver
            ? MessageProducer::ROUTE_PER_MESSAGE : MessageProducer::ROUTE_ALL_ASYNC;

        return $this->producers[$driver] = new AsyncMessageProducer($illuminateProducer, $routeStrategy);
    }

    protected function resolveMessageDecoratorMiddleware(array $decorators): Middleware
    {
        $decorators = array_merge($this->fromReporter('message.decorator', []), $decorators);

        return new ChainMessageDecoratorMiddleware(
            new ChainMessageDecorator(...$this->resolveServices($decorators))
        );
    }

    protected function resolveServices(array $services): array
    {
        foreach ($services as &$service) {
            if (is_string($service)) {
                $service = $this->container->make($service);
            }
        }

        return $services;
    }
}
