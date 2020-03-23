<?php

namespace Plexikon\Reporter\Contracts\Message;

use Plexikon\Reporter\Message\Message;

interface MessageFactory
{
    public function createMessageFrom($message): Message;
}
