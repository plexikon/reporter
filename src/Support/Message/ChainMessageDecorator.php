<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Support\Message;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Message\Message;

final class ChainMessageDecorator implements MessageDecorator
{
    private array $decorators;

    public function __construct(MessageDecorator ...$decorators)
    {
        $this->decorators = $decorators;
    }

    public function decorate(Message $message): Message
    {
        foreach ($this->decorators as $decorator) {
            $message = $decorator->decorate($message);
        }

        return $message;
    }
}
