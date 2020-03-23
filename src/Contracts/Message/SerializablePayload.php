<?php

namespace Plexikon\Reporter\Contracts\Message;

interface SerializablePayload
{
    public function toPayload(): array;

    public static function fromPayload(array $payload): SerializablePayload;
}
