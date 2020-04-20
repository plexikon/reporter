<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Producer;

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Plexikon\Reporter\CommandPublisher;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\EventPublisher;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\QueryPublisher;
use function get_class;

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

        $event = $message->event();

        if($event instanceof Messaging){
            $messageType = $event->messageType();

            switch ($messageType) {
                case Messaging::COMMAND:
                    return CommandPublisher::class;
                case Messaging::EVENT:
                    return EventPublisher::class;
                case Messaging::QUERY:
                    return QueryPublisher::class;
            }
        }

        throw new RuntimeException("Can not detect bus type from message event " . (get_class($event)));
    }
}
