<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Producer;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Publisher\Publisher;

final class MessageJob
{
    public ?string $connection;
    private ?string $queue;
    private array $payload;
    private string $busType;

    public function __construct(array $payload, string $busType, ?string $connection, ?string $queue)
    {
        $this->payload = $payload;
        $this->busType = $busType;
        $this->connection = $connection;
        $this->queue = $queue;
    }

    public function handle(Container $container): void
    {
        /** @var Publisher $serviceBus */
        $serviceBus = $container->get($this->busType);

        $serviceBus->dispatch($this->payload);
    }

    /**
     * @param Queue $queue
     * @param MessageJob $messageJob
     * @internal
     */
    public function queue(Queue $queue, MessageJob $messageJob): void
    {
        $queue->pushOn($this->queue, $messageJob);
    }

    public function displayName(): string
    {
        return $this->payload['headers'][MessageHeader::EVENT_TYPE];
    }
}
