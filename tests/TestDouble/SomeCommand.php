<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\TestDouble;

use Plexikon\Reporter\Command;

final class SomeCommand extends Command
{
    public static function withData(array $payload): self
    {
        return new self($payload);
    }
}
