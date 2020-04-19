<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Decorator;

use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Decorator\EventIdMessageDecorator;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class EventIdMessageDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_decorate_message_with_event_id(): void
    {
        $message = new Message(new \stdClass());

        $this->assertNull($message->header(MessageHeader::EVENT_ID));

        $decorator = new EventIdMessageDecorator();

        $message = $decorator->decorate($message);

        $this->assertTrue(Uuid::isValid($message->header(MessageHeader::EVENT_ID)));
    }

    /**
     * @test
     */
    public function it_does_not_decorate_message_if_event_id_already_exists(): void
    {
        $message = new Message(new \stdClass());

        $this->assertNull($message->header(MessageHeader::EVENT_ID));

        $message = $message->withHeader(MessageHeader::EVENT_ID, 'foo');

        $decorator = new EventIdMessageDecorator();

        $message = $decorator->decorate($message);

        $this->assertEquals('foo', $message->header(MessageHeader::EVENT_ID));
    }
}
