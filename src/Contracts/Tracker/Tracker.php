<?php

namespace Plexikon\Reporter\Contracts\Tracker;

use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tracker\TrackerContext;

interface Tracker
{
    public function newContext(string $event, ?callable $context = null): TrackerContext;

    public function fire(TrackerContext $context): void;

    public function subscribe(string $event, callable $callback);

    public function currentContext(): ?TrackerContext;

    public function setCurrentMessage(Message $message): void;
}
