<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Decorator;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Message;
use Ramsey\Uuid\Uuid;

final class EventIdMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        if (null === $message->header(MessageHeader::EVENT_ID)) {
            $message = $message->withHeader(
                MessageHeader::EVENT_ID,
                Uuid::uuid4()->toString()
            );
        }

        return $message;
    }
}
