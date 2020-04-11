<?php

namespace Plexikon\Reporter\Contracts\Message;

use Plexikon\Reporter\Message\Message;

interface MessageFactory
{
    /**
     * @param $message
     * @return Message
     */
    public function createMessageFrom($message): Message;
}
