<?php
declare(strict_types=1);

namespace Plexikon\Reporter;

use Plexikon\Reporter\Publisher\Publisher;
use React\Promise\PromiseInterface;

class QueryPublisher extends Publisher
{
    public function dispatch($message): PromiseInterface
    {
        return $this->dispatchMessage($message);
    }
}
