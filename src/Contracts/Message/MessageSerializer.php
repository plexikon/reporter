<?php

namespace Plexikon\Reporter\Contracts\Message;

use Generator;
use Plexikon\Reporter\Message\Message;

interface MessageSerializer
{
    /**
     * @param Message $message
     * @return array
     */
    public function serializeMessage(Message $message): array;

    /**
     * @param array $payload
     * @return Generator
     */
    public function unserializePayload(array $payload): Generator;
}
