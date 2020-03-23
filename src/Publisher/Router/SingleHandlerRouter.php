<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Router;

use Generator;

final class SingleHandlerRouter extends PublisherRouter
{
    protected function generateMessageHandler(array $messageHandlers): Generator
    {
        if (1 !== count($messageHandlers)) {
            throw new \RuntimeException("one unique handler is required");
        }

        foreach ($messageHandlers as $messageHandler) {
            yield $this->messageHandlerToCallable($messageHandler);
        }
    }
}
