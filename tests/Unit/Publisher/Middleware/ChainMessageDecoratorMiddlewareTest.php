<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Publisher\Middleware;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Middleware\ChainMessageDecoratorMiddleware;
use Plexikon\Reporter\Tests\Unit\TestCase;

class ChainMessageDecoratorMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_decorate_message(): void
    {
        $message = new Message(new \stdClass());

        $decorator = $this->prophesize(MessageDecorator::class);
        $decorator->decorate($message)->willReturn($message)->shouldBeCalled();

        $middleware = new ChainMessageDecoratorMiddleware($decorator->reveal());

        $returnedMessage = $middleware($message, fn(Message $message): Message => $message);

        $this->assertEquals($message, $returnedMessage);
    }

    private function somMessageDecorator(): MessageDecorator
    {
        return new class() implements MessageDecorator {
            public function decorate(Message $message): Message
            {
                return $message->withHeader('foo', 'bar');
            }
        };
    }

    private function anotherMessageDecorator(): MessageDecorator
    {
        return new class() implements MessageDecorator {
            public function decorate(Message $message): Message
            {
                return $message->withHeader('baz', 'foo_bar');
            }
        };
    }
}
