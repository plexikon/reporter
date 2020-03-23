<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Decorator;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Message;

final class AsyncMarkerMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        if (null === $message->header(MessageHeader::MESSAGE_ASYNC_MARKED)) {
            $message = $message->withHeader(
                MessageHeader::MESSAGE_ASYNC_MARKED,
                false
            );
        }

        return $message;
    }
}
