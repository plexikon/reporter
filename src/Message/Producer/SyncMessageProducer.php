<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Producer;

use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Message\Message;

final class SyncMessageProducer implements MessageProducer
{
    public function isMarkedAsync(Message $message): bool
    {
        return false;
    }

    public function mustBeHandledSync(Message $message): bool
    {
        return true;
    }

    public function produce(Message $message): Message
    {
        return $message;
    }
}
