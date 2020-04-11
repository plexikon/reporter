<?php

namespace Plexikon\Reporter\Contracts\Message;

interface PayloadSerializer
{
    /**
     * @param object $event
     * @return array
     */
    public function serializePayload(object $event): array;

    /**
     * @param string $className
     * @param array $payload
     * @return object
     */
    public function unserializePayload(string $className, array $payload): object;
}
