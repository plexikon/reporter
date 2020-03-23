<?php

namespace Plexikon\Reporter\Contracts\Publisher;

use Plexikon\Reporter\Message\Message;

interface Middleware
{
    public function __invoke(Message $message, callable $next);
}
