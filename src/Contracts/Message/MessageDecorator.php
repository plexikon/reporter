<?php

namespace Plexikon\Reporter\Contracts\Message;

use Plexikon\Reporter\Message\Message;

interface MessageDecorator
{
    /**
     * @param Message $message
     * @return Message
     */
    public function decorate(Message $message): Message;
}
