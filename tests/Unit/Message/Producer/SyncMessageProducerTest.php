<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Producer;

use Generator;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Message\Producer\SyncMessageProducer;
use Plexikon\Reporter\Tests\Unit\TestCase;

class SyncMessageProducerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideMessages
     * @param Message $message
     */
    public function it_return_always_false_on_marked_async(Message $message): void
    {
        $producer = new SyncMessageProducer();

        $this->assertFalse($producer->isMarkedAsync($message));
    }

    /**
     * @test
     * @dataProvider provideMessages
     * @param Message $message
     */
    public function it_return_always_true_on_must_be_handled_sync(Message $message): void
    {
        $producer = new SyncMessageProducer();

        $this->assertTrue($producer->mustBeHandledSync($message));
    }

    /**
     * @test
     * @dataProvider provideMessages
     * @param Message $message
     */
    public function it_return_same_message(Message $message): void
    {
        $producer = new SyncMessageProducer();

        $this->assertEquals($message, $producer->produce($message));
    }

    public function provideMessages(): Generator
    {
        yield[new Message(new \stdClass())];
        yield[new Message(new \stdClass()), [MessageHeader::MESSAGE_ASYNC_MARKED => true]];
        yield[new Message(new \stdClass()), [MessageHeader::MESSAGE_ASYNC_MARKED => false]];
    }
}
