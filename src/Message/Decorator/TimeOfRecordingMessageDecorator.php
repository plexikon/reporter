<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Decorator;

use Plexikon\Reporter\Contracts\Clock\Clock;
use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Message;

final class TimeOfRecordingMessageDecorator implements MessageDecorator
{
    private Clock $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function decorate(Message $message): Message
    {
        if (null === $message->header(MessageHeader::TIME_OF_RECORDING)) {
            $message = $message->withHeader(
                MessageHeader::TIME_OF_RECORDING,
                $this->clock->pointInTime()->toString()
            );
        }

        return $message;
    }
}
