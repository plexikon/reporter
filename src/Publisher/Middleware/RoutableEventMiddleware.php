<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Middleware;

use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Router\MultipleHandlersRouter;

final class RoutableEventMiddleware implements Middleware
{
    private MultipleHandlersRouter $router;
    private MessageProducer $messageProducer;

    public function __construct(MultipleHandlersRouter $router, MessageProducer $messageProducer)
    {
        $this->router = $router;
        $this->messageProducer = $messageProducer;
    }

    public function __invoke(Message $message, callable $next)
    {
        if ($this->messageProducer->mustBeHandledSync($message)) {
            foreach ($this->router->route($message) as $messageHandler) {
                if ($messageHandler) {
                    $messageHandler(
                       $message->isMessaging() ? $message->eventWithHeaders() : $message->event()
                    );
                }
            }

            return $next($message);
        }

        return $next($this->messageProducer->produce($message));
    }
}
