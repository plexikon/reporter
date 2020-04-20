<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Router;

use Generator;

class MultipleHandlersRouter extends PublisherRouter
{
    protected function generateMessageHandler(array $messageHandlers): Generator
    {
        foreach ($messageHandlers as $messageHandler) {
            yield $this->messageHandlerToCallable($messageHandler);
        }
    }
}
