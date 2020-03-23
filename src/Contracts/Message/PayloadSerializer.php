<?php

namespace Plexikon\Reporter\Contracts\Message;

interface PayloadSerializer
{
    public function serializePayload(object $event): array;

    public function unserializePayload(string $className, array $payload): object;
}
