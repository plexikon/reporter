<?php

namespace Plexikon\Reporter\Contracts\Message;

use Generator;
use Plexikon\Reporter\Message\Message;

interface MessageSerializer
{
    public function serializeMessage(Message $message): array;

    public function unserializePayload(array $payload): Generator;
}
