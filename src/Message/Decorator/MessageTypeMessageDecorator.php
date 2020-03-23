<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Decorator;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Message;

final class MessageTypeMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        if (null === $message->header(MessageHeader::MESSAGE_TYPE)) {
            $message = $message->withHeader(
                MessageHeader::MESSAGE_TYPE,
                $this->getMessageType($message)
            );
        }

        return $message;
    }

    private function getMessageType(Message $message): string
    {
        if ($message->isMessaging()) {
            return $message->event()->messageType();
        }

        return gettype($message->eventType());
    }
}
