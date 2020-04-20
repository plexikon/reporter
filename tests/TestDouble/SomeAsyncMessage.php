<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\TestDouble;

use Plexikon\Reporter\Contracts\Message\AsyncMessage;
use Plexikon\Reporter\Message\DomainMessage;

final class SomeAsyncMessage extends DomainMessage implements AsyncMessage
{
    public function messageType(): string
    {
        return 'some_type';
    }
}
