<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Decorator;

use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Decorator\EventTypeMessageDecorator;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\Unit\TestCase;

class EventTypeMessageDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_decorate_message_with_event_type(): void
    {
        $event = new \stdClass();

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToType($event)->willReturn('foo')->shouldBeCalled();

        $message = new Message($event);

        $this->assertNull($message->header(MessageHeader::EVENT_TYPE));

        $decorator = new EventTypeMessageDecorator($alias->reveal());

        $message = $decorator->decorate($message);

        $this->assertEquals('foo', $message->header(MessageHeader::EVENT_TYPE));
    }

    /**
     * @test
     */
    public function it_does_not_decorate_message_if_event_type_already_exists(): void
    {
        $event = new \stdClass();

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToType($event)->shouldNotBeCalled();

        $message = new Message($event);

        $this->assertNull($message->header(MessageHeader::EVENT_TYPE));

        $message = $message->withHeader(MessageHeader::EVENT_TYPE, 'bar');

        $decorator = new EventTypeMessageDecorator($alias->reveal());

        $message = $decorator->decorate($message);

        $this->assertEquals('bar', $message->header(MessageHeader::EVENT_TYPE));
    }
}
