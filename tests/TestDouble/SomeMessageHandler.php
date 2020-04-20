<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\TestDouble;

class SomeMessageHandler
{
    private bool $isMessageHandled = false;

    public function __invoke(): void
    {
        $this->isMessageHandled = true;
    }

    public function isMessageHandled(): bool
    {
        return $this->isMessageHandled;
    }
}
