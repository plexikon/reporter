<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Middleware;

use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Exception\MessageDispatchedFailure;
use Plexikon\Reporter\Message\Message;
use Throwable;

class PublisherExceptionMiddleware implements Middleware
{
    public function __invoke(Message $message, callable $next)
    {
        $currentMessage = $message;

        try {
            $message = $next($message);
        } catch (Throwable $e) {
            $exception = MessageDispatchedFailure::withException($e);
            $exception->setCurrentMessage($currentMessage);

            throw $exception;
        }

        return $message;
    }
}
