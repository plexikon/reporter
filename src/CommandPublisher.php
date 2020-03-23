<?php
declare(strict_types=1);

namespace Plexikon\Reporter;

use Plexikon\Reporter\Publisher\Publisher;

class CommandPublisher extends Publisher
{
    public function dispatch($message): void
    {
        $this->dispatchMessage($message);
    }
}
