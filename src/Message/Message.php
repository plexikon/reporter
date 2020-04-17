<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message;

use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\Exception\RuntimeException;

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

    public function eventWithHeaders(): Messaging
    {
        if (!$this->event instanceof Messaging) {
            throw new RuntimeException('Report headers to invalid event');
        }

        return $this->event->withHeaders($this->headers);
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
}
