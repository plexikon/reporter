<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Manager;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Contracts\Publisher\NamedPub;
use Plexikon\Reporter\Contracts\Publisher\Publisher;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Producer\AsyncMessageProducer;
use Plexikon\Reporter\Message\Producer\IlluminateProducer;
use Plexikon\Reporter\Publisher\Middleware\DefaultChainMessageDecoratorMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableCommandMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableEventMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableQuerySyncMiddleware;
use Plexikon\Reporter\Publisher\Publisher as AbstractPublisher;
use Plexikon\Reporter\Publisher\Router\MultipleHandlersRouter;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use Plexikon\Reporter\Support\Message\ChainMessageDecorator;

class PublisherManager
{
    private array $publishers = [];
    private array $producers = [];
    private array $reporter;
    private Application $app;

    public function __construct(Application $app)
    {
        $this->reporter = $app->get(Repository::class)->get('reporter');
        $this->app = $app;
    }

    public function command(string $name = null): Publisher
    {
        return $this->make($name ?? 'default', 'command');
    }

    public function query(string $name = null): Publisher
    {
        return $this->make($name ?? 'default', 'query');
    }

    public function event(string $name = null): Publisher
    {
        return $this->make($name ?? 'default', 'event');
    }

    public function make(string $publisherName, string $messageType): Publisher
    {
        $publisherKey = $messageType . '-' . $publisherName;

        if (isset($this->publishers[$publisherKey])) {
            return $this->publishers[$publisherKey];
        }

        return $this->publishers[$publisherKey] = $this->createPublisher($publisherName, $messageType);
    }

    protected function createPublisher(string $publisherName, string $messageType): Publisher
    {
        $pubConfig = $this->fromReporter("publisher.$messageType.$publisherName");

        if (!$pubConfig) {
            throw new RuntimeException("Invalid publisher with name $publisherName and type $messageType");
        }

        $publisherAbstract = $pubConfig['publisher'];

        if (!class_exists($publisherAbstract) && !$this->app->bound($publisherAbstract)) {
            throw new RuntimeException("Invalid publisher service $publisherAbstract");
        }

        $middleware = array_merge(
            $this->createPublisherMiddleware($pubConfig),
            [$this->createRoutableMiddleware($pubConfig)]
        );

        return $this->resolvePublisher($publisherAbstract, $middleware);
    }

    protected function resolvePublisher(string $publisherAbstract, array $middleware): Publisher
    {
        if (is_subclass_of($publisherAbstract, AbstractPublisher::class)) {
            /** @var Publisher $publisherAbstract */
            $publisher = new $publisherAbstract($this->app->get(MessageFactory::class), ...$middleware);

            if ($publisher instanceof NamedPub) {
                $publisher->setPublisherName($publisherAbstract);
            }

            return $publisher;
        }

        return $this->app->get($publisherAbstract);
    }

    protected function createPublisherMiddleware(array $publisherConfig): array
    {
        $middleware = array_merge(
            $this->fromReporter('middleware') ?? [],
            $publisherConfig['middleware'] ?? []
        );

        foreach ($middleware as &$_middleware) {
            if ($_middleware === DefaultChainMessageDecoratorMiddleware::class) {
                $_middleware = $this->createChainMessageDecorator($publisherConfig);
            }else{
                $_middleware = $this->app->make($_middleware);
            }
        }

        return $middleware;
    }

    protected function createChainMessageDecorator(array $publisherConfig): Middleware
    {
        $decorators = array_merge(
            $publisherConfig['message.decorator'] ?? [],
            $this->fromReporter('message.decorator') ?? []
        );

        foreach ($decorators as &$decorator) {
            $decorator = $this->app->make($decorator);
        }

        return new DefaultChainMessageDecoratorMiddleware(
            new ChainMessageDecorator(...$decorators)
        );
    }

    protected function createRoutableMiddleware(array $publisherConfig): Middleware
    {
        $messageAlias = $this->app->make(MessageAlias::class);
        $map = $publisherConfig['map'] ?? [];

        $handlerMethodName = $publisherConfig['handler_method'] ?? null;
        if ($handlerMethodName === '__invoke') {
            $handlerMethodName = null;
        }

        switch ($publisherConfig['router']) {
            case RoutableCommandMiddleware::class:
                return new RoutableCommandMiddleware(
                    new SingleHandlerRouter(
                        $map, $messageAlias, $this->app, $handlerMethodName
                    ),
                    $this->createMessageProducer($publisherConfig)
                );

            case RoutableEventMiddleware::class:
                return new RoutableEventMiddleware(
                    new MultipleHandlersRouter(
                        $map, $messageAlias, $this->app, $handlerMethodName
                    ),
                    $this->createMessageProducer($publisherConfig)
                );

            case RoutableQuerySyncMiddleware::class:
                return new RoutableQuerySyncMiddleware(
                    new SingleHandlerRouter($map, $messageAlias, $this->app, $handlerMethodName)
                );

            default:
                return $this->app->make($publisherConfig['router']);
        }
    }

    protected function createMessageProducer(array $publisherConfig): MessageProducer
    {
        $producerKey = $publisherConfig['route_strategy'];

        if (isset($this->producers[$producerKey])) {
            return $this->producers[$producerKey];
        }

        $producerConfig = $this->fromReporter("message.producer.$producerKey") ?? null;

        if (!$producerConfig) {
            throw new RuntimeException("Invalid producer strategy key $producerKey");
        }

        switch ($producerKey) {
            case 'sync':
                $producer = $this->app->make($producerConfig['abstract']);
                break;
            case 'async':
            case 'async_all':
                $producer = $this->resolveAsyncMessageProducer($producerConfig);
                break;
            default:
                $producer = $this->app->make($producerConfig['abstract']);
        }

        return $this->producers[$producerKey] = $producer;
    }

    protected function resolveAsyncMessageProducer(array $producerConfig): MessageProducer
    {
        if (AsyncMessageProducer::class === $producerConfig['abstract']) {
            return new AsyncMessageProducer(
                new IlluminateProducer(
                    $this->app->get(QueueingDispatcher::class),
                    $this->app->get(MessageSerializer::class),
                    $producerConfig['connection'] ?? null,
                    $producerConfig['queue'] ?? null,
                ),
                $producerConfig['strategy']
            );
        }

        return $this->app->get($producerConfig['abstract']);
    }

    protected function fromReporter(string $key)
    {
        return Arr::get($this->reporter, $key);
    }
}
