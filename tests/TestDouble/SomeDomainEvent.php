<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\TestDouble;

use Plexikon\Reporter\DomainEvent;

final class SomeDomainEvent extends DomainEvent
{
    public static function withData(array $payload): self
    {
        return new self($payload);
    }
}
