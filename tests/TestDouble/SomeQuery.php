<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\TestDouble;

use Plexikon\Reporter\Query;

final class SomeQuery extends Query
{
    public static function withData(array $payload): self
    {
        return new self($payload);
    }
}
