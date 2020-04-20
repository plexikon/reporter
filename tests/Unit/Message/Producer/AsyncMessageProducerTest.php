<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Producer;

use Plexikon\Reporter\Contracts\Message\AsyncMessage;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Contracts\Message\SerializablePayload;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Message\Producer\AsyncMessageProducer;
use Plexikon\Reporter\Message\Producer\IlluminateProducer;
use Plexikon\Reporter\Tests\TestDouble\SomeAsyncMessage;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\Unit\TestCase;
use stdClass;

class AsyncMessageProducerTest extends TestCase
{
    /**
     * @test
     */
    public function it_produce_message_sync_if_message__is_not_an_instance_of_serializable_payload()
    {
        $message = new Message(new stdClass());
        $producer = $this->prophesize(IlluminateProducer::class);
        $producer->handle($message)->shouldNotBeCalled();

        $messageProducer = new AsyncMessageProducer($producer->reveal(), 'foo');
        $messageProducer->produce($message);
    }

    /**
     * @test
     */
    public function it_produce_message_sync_if_message_is_already_marked_async()
    {
        $message = new Message($event = SomeDomainEvent::fromPayload([]), [
            MessageHeader::MESSAGE_ASYNC_MARKED => true
        ]);

        $this->assertInstanceOf(SerializablePayload::class, $event);

        $producer = $this->prophesize(IlluminateProducer::class);
        $producer->handle($message)->shouldNotBeCalled();

        $messageProducer = new AsyncMessageProducer($producer->reveal(), 'foo');
        $messageProducer->produce($message);
    }

    /**
     * @test
     */
    public function it_produce_message_sync_if_route_strategy_match_none_async()
    {
        $message = new Message($event = SomeDomainEvent::fromPayload([]), [
            MessageHeader::MESSAGE_ASYNC_MARKED => false
        ]);

        $this->assertInstanceOf(SerializablePayload::class, $event);

        $producer = $this->prophesize(IlluminateProducer::class);
        $producer->handle($message)->shouldNotBeCalled();

        $messageProducer = new AsyncMessageProducer($producer->reveal(), MessageProducer::ROUTE_NONE_ASYNC);
        $messageProducer->produce($message);
    }

    /**
     * @test
     */
    public function it_produce_message_async_if_route_strategy_match_per_message_and_message_instance_of_async_message()
    {
        $message = new Message($event = SomeAsyncMessage::fromPayload([]), [
            MessageHeader::MESSAGE_ASYNC_MARKED => false
        ]);

        $markedMessage = new Message($event = SomeAsyncMessage::fromPayload([]), [
            MessageHeader::MESSAGE_ASYNC_MARKED => true
        ]);

        $this->assertInstanceOf(AsyncMessage::class, $event);
        $this->assertInstanceOf(SerializablePayload::class, $event);

        $producer = $this->prophesize(IlluminateProducer::class);
        $producer->handle($markedMessage)->shouldBeCalled();

        $messageProducer = new AsyncMessageProducer($producer->reveal(), MessageProducer::ROUTE_PER_MESSAGE);
        $messageProducer->produce($message);
    }

    /**
     * @test
     */
    public function it_produce_message_async_if_route_strategy_match_all_async(): void
    {
        $message = new Message($event = SomeAsyncMessage::fromPayload([]), [
            MessageHeader::MESSAGE_ASYNC_MARKED => false
        ]);

        $markedMessage = new Message($event = SomeAsyncMessage::fromPayload([]), [
            MessageHeader::MESSAGE_ASYNC_MARKED => true
        ]);

        $this->assertInstanceOf(AsyncMessage::class, $event);
        $this->assertInstanceOf(SerializablePayload::class, $event);

        $producer = $this->prophesize(IlluminateProducer::class);
        $producer->handle($markedMessage)->shouldBeCalled();

        $messageProducer = new AsyncMessageProducer($producer->reveal(), MessageProducer::ROUTE_ALL_ASYNC);
        $messageProducer->produce($message);
    }

    /**
     * @test
     */
    public function it_raise_exception_with_invalid_strategy(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Unable to determine producer with strategy foo_bar');

        $message = new Message($event = SomeAsyncMessage::fromPayload([]), [
            MessageHeader::MESSAGE_ASYNC_MARKED => false
        ]);

        $this->assertInstanceOf(AsyncMessage::class, $event);
        $this->assertInstanceOf(SerializablePayload::class, $event);

        $producer = $this->prophesize(IlluminateProducer::class);
        $producer->handle()->shouldNotBeCalled();

        $messageProducer = new AsyncMessageProducer($producer->reveal(), 'foo_bar');
        $messageProducer->produce($message);
    }
}
