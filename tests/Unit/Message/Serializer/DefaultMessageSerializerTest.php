<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Serializer;

use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\PayloadSerializer;
use Plexikon\Reporter\Exception\InvalidArgumentException;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Message\Serializer\DefaultMessageSerializer;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\Unit\TestCase;

class DefaultMessageSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_serialize_message(): void
    {
        $event = SomeDomainEvent::fromPayload(['foo' => 'bar']);
        $message = new Message($event, [
            'some_header' => 'some_value'
        ]);

        $alias = $this->prophesize(MessageAlias::class);
        $payloadSerializer = $this->prophesize(PayloadSerializer::class);
        $payloadSerializer->serializePayload($event)->willReturn(['foo' => 'bar']);

        $serializer = new DefaultMessageSerializer($alias->reveal(), $payloadSerializer->reveal());
        $unserialized = $serializer->serializeMessage($message);

        $this->assertEquals([
            'headers' => ['some_header' => 'some_value'],
            'payload' => ['foo' => 'bar']
        ], $unserialized);
    }

    /**
     * @test
     */
    public function it_serializer_message(): void
    {
        $payload = [
            'headers' => [
                MessageHeader::EVENT_TYPE => 'class_name'
            ],
            'payload' => ['baz' => 'baz']
        ];

        $event = SomeDomainEvent::fromPayload(['foo' => 'bar']);

        $alias = $this->prophesize(MessageAlias::class);
        $alias->typeToClass('class_name')->willReturn('class_name');

        $payloadSerializer = $this->prophesize(PayloadSerializer::class);
        $payloadSerializer->unserializePayload('class_name', ['baz' =>'baz'])->willReturn($event);

        $serializer = new DefaultMessageSerializer($alias->reveal(), $payloadSerializer->reveal());

        $message = $serializer->unserializePayload($payload)->current();

        $this->assertInstanceOf(Message::class, $message);
    }

    /**
     * @test
     */
    public function it_raise_exception_if_header_key_missing_in_payload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Headers key missing');

        $payload = [
            'payload' => ['baz' => 'baz']
        ];

        $alias = $this->prophesize(MessageAlias::class);
        $payloadSerializer = $this->prophesize(PayloadSerializer::class);

        $serializer = new DefaultMessageSerializer($alias->reveal(), $payloadSerializer->reveal());
        $serializer->unserializePayload($payload)->current();
    }

    /**
     * @test
     */
    public function it_raise_exception_if_payload_key_missing_in_payload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Payload key missing');

        $payload = [
            'headers' => ['baz' => 'baz']
        ];

        $alias = $this->prophesize(MessageAlias::class);
        $payloadSerializer = $this->prophesize(PayloadSerializer::class);

        $serializer = new DefaultMessageSerializer($alias->reveal(), $payloadSerializer->reveal());
        $serializer->unserializePayload($payload)->current();
    }
}
