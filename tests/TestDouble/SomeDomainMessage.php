<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\TestDouble;

use Plexikon\Reporter\Message\DomainMessage;

class SomeDomainMessage extends DomainMessage
{
    public function messageType(): string
    {
       return 'some_type';
    }
}
