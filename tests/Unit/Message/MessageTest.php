<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message;

use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\Unit\TestCase;

class MessageTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_object_event(): void
    {
        $message = new Message(new \stdClass());

        $this->assertEquals(new \stdClass(), $message->event());

        $this->assertEmpty($message->headers());
    }

    /**
     * @test
     */
    public function it_can_be_constructed_with_headers(): void
    {
        $message = new Message(new \stdClass(), ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $message->headers());
    }

    /**
     * @test
     */
    public function it_can_add_header(): void
    {
        $message = new Message(new \stdClass(), ['foo' => 'bar']);
        $message = $message->withHeader('baz', 'foo_bar');

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'foo_bar'
        ], $message->headers());
    }

    /**
     * @test
     */
    public function it_can_add_headers(): void
    {
        $message = new Message(new \stdClass(), ['foo' => 'bar']);
        $message = $message->withHeaders([
            'baz1' => 'baz2',
            'baz3' => 'baz4'
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'baz1' => 'baz2',
            'baz3' => 'baz4'
        ], $message->headers());
    }

    /**
     * @test
     */
    public function it_return_event_with_headers(): void
    {
        $message = new Message($event = SomeDomainEvent::fromPayload([]), $headers = ['foo' => 'bar']);

        $this->assertEquals($headers, $message->eventWithHeaders()->headers());
    }

    /**
     * @test
     */
    public function it_raise_exception_if_event_is_not_an_instance_of_messaging(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Report headers to invalid event');

        $message = new Message(new \stdClass());

        $message->eventWithHeaders();
    }

    /**
     * @test
     */
    public function it_return_false_if_event_is_not_an_instance_of_messaging(): void
    {
        $message = new Message(new \stdClass());

        $this->assertFalse($message->isMessaging());
    }

    /**
     * @test
     */
    public function it_return_true_if_event_is_an_instance_of_messaging(): void
    {
        $message = new Message($event = SomeDomainEvent::fromPayload([]));

        $this->assertTrue($message->isMessaging());
    }
}
