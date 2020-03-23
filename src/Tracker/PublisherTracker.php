<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tracker;

use Plexikon\Reporter\Contracts\Tracker\Tracker;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tracker\Event\EventDispatched;
use Plexikon\Reporter\Tracker\Event\EventFinalized;

final class PublisherTracker implements Tracker
{
    private array $events;
    private array $subscribers = [];
    private ?Message $currentMessage = null;
    private ?TrackerContext $currentContext = null;

    public function __construct()
    {
        $this->events = [EventDispatched::class, EventFinalized::class];
    }

    public function newContext(string $event, ?callable $context = null): TrackerContext
    {
        $context = new TrackerContext($event, $context);
        if ($this->currentMessage) {
            $context->setMessage($this->currentMessage);
        }

        return $context;
    }

    public function fire(TrackerContext $context): void
    {
        assert(array_key_exists($context->eventName(), $this->subscribers));

        foreach ($this->subscribers[$context->eventName()] as $callback) {
            $callback($context);

            if ($context->isPropagationStopped()) {
                return;
            }
        }
    }

    public function subscribe(string $event, callable $callback)
    {
        assert(in_array($event, $this->events));

        $this->subscribers[$event][] = $callback;
    }

    public function setCurrentMessage(Message $message): void
    {
        $this->currentMessage = $message;
    }

    public function currentContext(): ?TrackerContext
    {
        return clone $this->currentContext;
    }
}
