<?php
declare(strict_types=1);

namespace Plexikon\Reporter;

use Plexikon\Reporter\Message\DomainMessage;

abstract class Command extends DomainMessage
{
    public function messageType(): string
    {
       return self::COMMAND;
    }
}
