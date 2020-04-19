<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Support\Message;

use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Support\Message\NoOpMessageDecorator;
use Plexikon\Reporter\Tests\Unit\TestCase;

class NoOpMessageDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_return_message_untouched(): void
    {
        $decorator = new NoOpMessageDecorator();

        $message = new Message(new \stdClass(), ['foo' => 'bar']);

        $message = $decorator->decorate($message);

        $this->assertEquals(['foo' => 'bar'], $message->headers());
    }
}
