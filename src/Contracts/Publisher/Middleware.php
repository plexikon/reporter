<?php

namespace Plexikon\Reporter\Contracts\Publisher;

use Plexikon\Reporter\Message\Message;

interface Middleware
{
    /**
     * @param Message $message
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Message $message, callable $next);
}
