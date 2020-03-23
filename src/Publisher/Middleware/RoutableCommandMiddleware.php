<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Middleware;

use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;

final class RoutableCommandMiddleware implements Middleware
{
    private SingleHandlerRouter $router;
    private MessageProducer $messageProducer;

    public function __construct(SingleHandlerRouter $router, MessageProducer $messageProducer)
    {
        $this->router = $router;
        $this->messageProducer = $messageProducer;
    }

    public function __invoke(Message $message, callable $next)
    {
        if ($this->messageProducer->mustBeHandledSync($message)) {
            $messageHandler = $this->router->route($message)->current();

            $messageHandler($message->event());

            return $next($message);
        }

        return $next($this->messageProducer->produce($message));
    }
}
