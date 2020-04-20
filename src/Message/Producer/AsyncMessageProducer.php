<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Producer;

use Plexikon\Reporter\Contracts\Message\AsyncMessage;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Contracts\Message\SerializablePayload;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Message;

final class AsyncMessageProducer implements MessageProducer
{
    private IlluminateProducer $illuminateProducer;
    private string $producerStrategy;

    public function __construct(IlluminateProducer $illuminateProducer, string $producerStrategy)
    {
        $this->illuminateProducer = $illuminateProducer;
        $this->producerStrategy = $producerStrategy;
    }

    public function produce(Message $message): Message
    {
        if ($this->mustBeHandledSync($message)) {
            return $message;
        }

        return $this->produceMessageAsync($message);
    }

    public function mustBeHandledSync(Message $message): bool
    {
        if (!$message->event() instanceof SerializablePayload) {
            return true;
        }

        if ($this->isMarkedAsync($message)) {
            return true;
        }

        if ($this->producerStrategy === self::ROUTE_NONE_ASYNC) {
            return true;
        }

        if ($this->producerStrategy === self::ROUTE_PER_MESSAGE) {
            return !$message->event() instanceof AsyncMessage;
        }

        if ($this->producerStrategy === self::ROUTE_ALL_ASYNC) {
            return false;
        }

        throw new RuntimeException("Unable to determine producer with strategy " . $this->producerStrategy);
    }

    private function produceMessageAsync(Message $message): Message
    {
        $message = $message->withHeader(MessageHeader::MESSAGE_ASYNC_MARKED, true);

        $this->illuminateProducer->handle($message);

        return $message;
    }

    private function isMarkedAsync(Message $message): bool
    {
        return true === $message->header(MessageHeader::MESSAGE_ASYNC_MARKED);
    }
}
