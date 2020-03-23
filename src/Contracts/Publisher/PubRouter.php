<?php

namespace Plexikon\Reporter\Contracts\Publisher;

use Generator;
use Plexikon\Reporter\Message\Message;

interface PubRouter
{
    public function route(Message $message): Generator;
}
