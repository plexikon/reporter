<?php

namespace Plexikon\Reporter\Contracts\Message;

interface Messaging extends SerializablePayload
{
    public const COMMAND = 'command';

    public const QUERY = 'query';

    public const EVENT = 'event';

    /**
     * @return string
     */
    public function messageType(): string;
}
