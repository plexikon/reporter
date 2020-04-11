<?php

namespace Plexikon\Reporter\Contracts\Publisher;

use Generator;
use Plexikon\Reporter\Message\Message;

interface PubRouter
{
    /**
     * @param Message $message
     * @return Generator
     */
    public function route(Message $message): Generator;
}
