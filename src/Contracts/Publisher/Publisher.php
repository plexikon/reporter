<?php

namespace Plexikon\Reporter\Contracts\Publisher;

use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\Message\Message;

interface Publisher
{
    /**
     * @param Message|Messaging|object|array $message
     * @return mixed
     */
    public function dispatch($message);
}
