<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Router;

use Generator;
use Plexikon\Reporter\Exception\PublisherFailure;

class SingleHandlerRouter extends PublisherRouter
{
    protected function generateMessageHandler(array $messageHandlers): Generator
    {
        if (1 !== count($messageHandlers)) {
            throw PublisherFailure::routerSupportAndRequireOneHandlerOnly(static::class);
        }

        foreach ($messageHandlers as $messageHandler) {
            yield $this->messageHandlerToCallable($messageHandler);
        }
    }
}
