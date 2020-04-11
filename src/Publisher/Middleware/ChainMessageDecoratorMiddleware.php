<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Middleware;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Message\Message;

final class ChainMessageDecoratorMiddleware implements Middleware
{
    private MessageDecorator $messageDecorator;

    public function __construct(MessageDecorator $messageDecorator)
    {
        $this->messageDecorator = $messageDecorator;
    }

    public function __invoke(Message $message, callable $next)
    {
        $message = $this->messageDecorator->decorate($message);

        return $next($message);
    }
}
