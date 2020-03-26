<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Support\Message;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Message\Message;

final class NoOpMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        return $message;
    }
}
