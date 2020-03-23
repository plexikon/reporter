<?php

namespace Plexikon\Reporter\Contracts\Message;

use Plexikon\Reporter\Message\Message;

interface MessageDecorator
{
    public function decorate(Message $message): Message;
}
