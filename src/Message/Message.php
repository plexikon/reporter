<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message;

use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\Messaging;

final class Message
{
    private object $event;
    private array $headers;

    public function __construct(object $event, array $headers = [])
    {
        $this->event = $event;
        $this->headers = $headers;
    }

    public function event(): object
    {
        return $this->event;
    }

    public function withHeader(string $key, $value): Message
    {
        $clone = clone $this;
        $clone->headers[$key] = $value;

        return $clone;
    }

    public function withHeaders(array $headers): Message
    {
        $clone = clone $this;
        $clone->headers = $headers + $clone->headers;

        return $clone;
    }

    public function header(string $key)
    {
        return $this->headers[$key] ?? null;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function isMessaging(): bool
    {
        return $this->event instanceof Messaging;
    }

    public function eventType(): string
    {
        return $this->headers[MessageHeader::EVENT_TYPE];
    }

    public function eventId(): string
    {
        return $this->headers[MessageHeader::EVENT_ID];
    }

    public function timeOfRecording(): string
    {
        return $this->headers[MessageHeader::TIME_OF_RECORDING];
    }

    public function messageType(): string
    {
        return $this->headers[MessageHeader::MESSAGE_TYPE];
    }

    public function busType(): string
    {
        return $this->headers[MessageHeader::MESSAGE_BUS_TYPE];
    }
}
