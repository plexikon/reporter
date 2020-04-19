<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Support\Message;

use Plexikon\Reporter\Contracts\Message\MessageDecorator;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Support\Message\ChainMessageDecorator;
use Plexikon\Reporter\Tests\Unit\TestCase;

class ChainMessageDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_chain_message_decorators(): void
    {
        $chain = new ChainMessageDecorator(
            $this->somMessageDecorator(),
            $this->anotherMessageDecorator()
        );

        $message = new Message(new \stdClass());

        $this->assertEmpty($message->headers());

        $message = $chain->decorate($message);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'foo_bar'
        ], $message->headers());
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
