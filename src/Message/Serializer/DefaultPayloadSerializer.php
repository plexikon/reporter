<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Serializer;

use Plexikon\Reporter\Contracts\Message\PayloadSerializer;
use Plexikon\Reporter\Contracts\Message\SerializablePayload;

final class DefaultPayloadSerializer implements PayloadSerializer
{
    public function serializePayload(object $event): array
    {
        return $event->toPayload();
    }

    public function unserializePayload(string $className, array $payload): object
    {
        /* @var SerializablePayload $className */
        return $className::fromPayload($payload);
    }
}
