<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Router;

use Closure;
use Generator;
use Illuminate\Contracts\Container\Container;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Publisher\PubRouter;
use Plexikon\Reporter\Exception\PublisherFailure;
use Plexikon\Reporter\Message\Message;

abstract class PublisherRouter implements PubRouter
{
    private iterable $map;
    private MessageAlias $messageAlias;
    private ?Container $container;
    private ?string $callableMethod;

    public function __construct(iterable $map,
                                MessageAlias $messageAlias,
                                ?Container $container = null,
                                ?string $callableMethod = null)
    {
        $this->map = $map;
        $this->messageAlias = $messageAlias;
        $this->container = $container;
        $this->callableMethod = $callableMethod;
    }

    public function route(Message $message): Generator
    {
        $messageHandlers = $this->determineMessageHandler($message);

        return $this->generateMessageHandler($messageHandlers);
    }

    abstract protected function generateMessageHandler(array $messageHandlers): Generator;

    protected function messageHandlerToCallable($messageHandler): callable
    {
        if (is_string($messageHandler)) {
            if (!$this->container) {
                throw PublisherFailure::missingContainerForMessageHandler($messageHandler);
            }

            $messageHandler = $this->container->make($messageHandler);
        }

        if (is_callable($messageHandler)) {
            return $messageHandler;
        }

        if ($this->callableMethod && method_exists($messageHandler, $this->callableMethod)) {
            return Closure::fromCallable([$messageHandler, $this->callableMethod]);
        }

        throw PublisherFailure::unsupportedMessageHandler($messageHandler);
    }

    private function determineMessageHandler(Message $message): array
    {
        $messageAlias = $this->messageAlias->instanceToAlias($message->event());

        if (!$messageHandlers = $this->map[$messageAlias] ?? false) {
            throw PublisherFailure::messageNameNotFoundInMap($messageAlias);
        }

        if (!is_array($messageHandlers)) {
            $messageHandlers = [$messageHandlers];
        }

        return $messageHandlers;
    }
}
