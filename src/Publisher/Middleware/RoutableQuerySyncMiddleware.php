<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Middleware;

use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Throwable;

final class RoutableQuerySyncMiddleware implements Middleware
{
    private SingleHandlerRouter $router;

    public function __construct(SingleHandlerRouter $router)
    {
        $this->router = $router;
    }

    public function __invoke(Message $message, callable $next): PromiseInterface
    {
        $messageHandler = $this->router->route($message)->current();
        $deferred = new Deferred();

        try {
            $messageHandler(
                $message->isMessaging() ? $message->eventWithHeaders() : $message->event(),
                $deferred
            );
        } catch (Throwable $exception) {
            $deferred->reject($exception);
        } finally {
            return $deferred->promise();
        }
    }
}
