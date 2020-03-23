<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Producer;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Plexikon\Reporter\CommandPublisher;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\EventPublisher;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\QueryPublisher;
use RuntimeException;

class IlluminateProducer
{
    private QueueingDispatcher $queueingDispatcher;
    private MessageSerializer $messageSerializer;
    private ?string $connection;
    private ?string $queue;

    public function __construct(QueueingDispatcher $queueingDispatcher,
                                MessageSerializer $messageSerializer,
                                ?string $connection,
                                ?string $queue)
    {
        $this->queueingDispatcher = $queueingDispatcher;
        $this->messageSerializer = $messageSerializer;
        $this->connection = $connection;
        $this->queue = $queue;
    }

    public function handle(Message $message): void
    {
        $payload = $this->messageSerializer->serializeMessage($message);

        $messageJob = $this->toMessageJob($payload, $this->detectBusType($message));

        $this->queueingDispatcher->dispatchToQueue($messageJob);
    }

    private function toMessageJob(array $payload, string $busType): MessageJob
    {
        return new MessageJob($payload, $busType, $this->connection, $this->queue);
    }

    private function detectBusType(Message $message): string
    {
        $namedBus = $message->header(MessageHeader::MESSAGE_BUS_TYPE);

        if (is_string($namedBus)) {
            return $namedBus;
        }

        $messageType = $message->header(MessageHeader::MESSAGE_TYPE);

        switch ($messageType) {
            case Messaging::COMMAND:
                return CommandPublisher::class;
            case Messaging::EVENT:
                return EventPublisher::class;
            case Messaging::QUERY:
                return QueryPublisher::class;

            default:
                throw new RuntimeException("Can not detect bus type from message type $messageType");
        }
    }
}
