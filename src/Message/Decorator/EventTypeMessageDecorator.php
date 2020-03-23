<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Decorator;

use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Message;

final class EventTypeMessageDecorator implements MessageDecorator
{
    private MessageAlias $messageAlias;

    public function __construct(MessageAlias $messageAlias)
    {
        $this->messageAlias = $messageAlias;
    }

    public function decorate(Message $message): Message
    {
        if (null === $message->header(MessageHeader::EVENT_TYPE)) {
            $message = $message->withHeader(
                MessageHeader::EVENT_TYPE,
                $this->messageAlias->instanceToType($message->event())
            );
        }

        return $message;
    }
}
