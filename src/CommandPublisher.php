<?php
declare(strict_types=1);

namespace Plexikon\Reporter;

use Plexikon\Reporter\Publisher\Publisher;
use Throwable;

class CommandPublisher extends Publisher
{
    private array $queue = [];
    private bool $isDispatching = false;

    public function dispatch($message): void
    {
        $this->queue[] = $message;

        if (!$this->isDispatching) {
            $this->isDispatching = true;

            try {
                while ($command = array_shift($this->queue)) {
                    $this->dispatchMessage($command);
                }
            } catch (Throwable $exception) {
                $this->isDispatching = false;

                throw $exception;
            }

            $this->isDispatching = false;
        }
    }
}
