<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher;

use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Contracts\Publisher\NamedPub;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Concerns\HasNamedPub;

abstract class Publisher implements NamedPub
{
    use HasNamedPub;

    /**
     * @var callable
     */
    private $middlewareChain;
    private MessageFactory $messageFactory;

    public function __construct(MessageFactory $messageFactory, Middleware ...$middleware)
    {
        $this->messageFactory = $messageFactory;
        $this->middlewareChain = $this->nextMiddleware($middleware);
    }

    /**
     * @param mixed $message
     * @return mixed
     */
    protected function dispatchMessage($message)
    {
        $message = $this->messageFactory->createMessageFrom($message)
            ->withHeader(MessageHeader::MESSAGE_BUS_TYPE, $this->publisherName());

        return ($this->middlewareChain)($message);
    }

    private function nextMiddleware(array $middlewareList): callable
    {
        $last = function (): void {
        };

        while ($middleware = array_pop($middlewareList)) {
            $last = function (Message $message) use ($middleware, $last) {
                return ($middleware)($message, $last);
            };
        }

        return $last;
    }
}
