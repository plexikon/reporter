<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tracker;

use Plexikon\Reporter\Message\Message;
use Throwable;

class TrackerContext
{
    private ?string $eventName = null;
    private ?Message $message = null;
    private ?Throwable $exception = null;
    private bool $isPropagationStopped = false;

    public function __construct(string $eventName, ?callable $callback)
    {
        $this->eventName = $eventName;

        if ($callback) {
            $callback($this);
        }
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function setMessage(Message $message): void
    {
        $this->message = $message;
    }

    public function message(): ?Message
    {
        return $this->message;
    }

    public function hasMessage(): bool
    {
        return $this->message instanceof Message;
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }

    public function stopPropagation(bool $stopDispatching): void
    {
        $this->isPropagationStopped = $stopDispatching;
    }

    public function setException(Throwable $exception): void
    {
        $this->exception = $exception;
    }

    public function exception(): ?Throwable
    {
        return $this->exception;
    }

    public function hasException(): bool
    {
        return $this->exception instanceof Throwable;
    }
}
