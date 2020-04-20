<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Producer;

use Generator;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Message\Producer\IlluminateProducer;
use Plexikon\Reporter\Message\Producer\MessageJob;
use Plexikon\Reporter\Tests\TestDouble\SomeCommand;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainMessage;
use Plexikon\Reporter\Tests\TestDouble\SomeQuery;
use Plexikon\Reporter\Tests\Unit\TestCase;
use Prophecy\Argument;

class IlluminateProducerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideDomainMessageForPublisherDetection
     * @param Messaging $event
     */
    public function it_handle_and_dispatch_payload_from_publisher_type_detection(Messaging $event): void
    {
        $payload = ['foo' => 'bar'];
        $message = new Message($event);

        $serializer = $this->prophesize(MessageSerializer::class);
        $serializer->serializeMessage($message)->willReturn($payload)->shouldBeCalled();

        $dispatcher = $this->prophesize(QueueingDispatcher::class);
        $dispatcher->dispatchToQueue(Argument::type(MessageJob::class))->shouldBeCalled();

        $producer = new IlluminateProducer($dispatcher->reveal(), $serializer->reveal(), null, null);
        $producer->handle($message);
    }

    /**
     * @test
     */
    public function it_handle_and_dispatch_payload_from_bus_type_header_message(): void
    {
        $payload = ['foo' => 'bar'];
        $message = new Message(new \stdClass(), [
            MessageHeader::MESSAGE_BUS_TYPE => 'bar'
        ]);

        $serializer = $this->prophesize(MessageSerializer::class);
        $serializer->serializeMessage($message)->willReturn($payload)->shouldBeCalled();

        $dispatcher = $this->prophesize(QueueingDispatcher::class);
        $dispatcher->dispatchToQueue(Argument::type(MessageJob::class))->shouldBeCalled();

        $producer = new IlluminateProducer($dispatcher->reveal(), $serializer->reveal(), null, null);
        $producer->handle($message);
    }

    /**
     * @test
     */
    public function it_raise_exception_if_publisher_type_can_not_be_detected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessage('Can not detect bus type from message event ' . SomeDomainMessage::class);

        $payload = ['foo' => 'bar'];
        $message = new Message(SomeDomainMessage::fromPayload([]));

        $serializer = $this->prophesize(MessageSerializer::class);
        $serializer->serializeMessage($message)->willReturn($payload)->shouldBeCalled();

        $dispatcher = $this->prophesize(QueueingDispatcher::class);
        $dispatcher->dispatchToQueue()->shouldNotBeCalled();

        $producer = new IlluminateProducer($dispatcher->reveal(), $serializer->reveal(), null, null);
        $producer->handle($message);
    }

    public function provideDomainMessageForPublisherDetection(): Generator
    {
        yield [SomeDomainEvent::fromPayload([])];
        yield [SomeCommand::fromPayload([])];
        yield [SomeQuery::fromPayload([])];
    }
}
