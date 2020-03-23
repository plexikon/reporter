<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message;

use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\Contracts\Message\SerializablePayload;

abstract class DomainMessage implements Messaging
{
    private array $payload;

    protected function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function toPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     * @return SerializablePayload|Messaging
     */
    public static function fromPayload(array $payload): SerializablePayload
    {
        return new static($payload);
    }
}
