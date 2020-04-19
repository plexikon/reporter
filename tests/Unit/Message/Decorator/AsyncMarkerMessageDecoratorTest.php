<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Decorator;

use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Decorator\AsyncMarkerMessageDecorator;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\Unit\TestCase;

class AsyncMarkerMessageDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_decorate_message_with_async_marker(): void
    {
        $message = new Message(new \stdClass());

        $this->assertNull($message->header(MessageHeader::MESSAGE_ASYNC_MARKED));

        $decorator = new AsyncMarkerMessageDecorator();

        $message = $decorator->decorate($message);

        $this->assertFalse($message->header(MessageHeader::MESSAGE_ASYNC_MARKED));
    }

    /**
     * @test
     */
    public function it_does_not_decorate_message_if_async_marker_already_exists(): void
    {
        $message = new Message(new \stdClass());
        $this->assertNull($message->header(MessageHeader::MESSAGE_ASYNC_MARKED));

        $message = $message->withHeader(MessageHeader::MESSAGE_ASYNC_MARKED, true);

        $decorator = new AsyncMarkerMessageDecorator();

        $message = $decorator->decorate($message);

        $this->assertTrue($message->header(MessageHeader::MESSAGE_ASYNC_MARKED));
    }
}
