<?php

namespace Plexikon\Reporter\Contracts\Message;

interface SerializablePayload
{
    /**
     * @return array
     */
    public function toPayload(): array;

    /**
     * @param array $payload
     * @return SerializablePayload
     */
    public static function fromPayload(array $payload): SerializablePayload;
}
