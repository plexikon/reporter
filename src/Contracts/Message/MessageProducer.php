<?php

namespace Plexikon\Reporter\Contracts\Message;

use Plexikon\Reporter\Message\Message;

interface MessageProducer
{
    public const ROUTE_ALL_ASYNC = '__route_all_async';
    public const ROUTE_NONE_ASYNC = '__route_none_async';
    public const ROUTE_PER_MESSAGE = '__route_per_message';

    public function isMarkedAsync(Message $message): bool;

    public function mustBeHandledSync(Message $message): bool;

    public function produce(Message $message): Message;
}
