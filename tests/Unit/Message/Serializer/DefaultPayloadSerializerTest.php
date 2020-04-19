<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Serializer;

use Plexikon\Reporter\Message\Serializer\DefaultPayloadSerializer;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainMessage;
use Plexikon\Reporter\Tests\Unit\TestCase;

class DefaultPayloadSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_serialize_payload(): void
    {
        $event = SomeDomainMessage::fromPayload(['foo' => 'bar']);

        $serializer = new DefaultPayloadSerializer();

        $this->assertEquals($event->toPayload(), $serializer->serializePayload($event));
    }

    /**
     * @test
     */
    public function it_unserialize_payload(): void
    {
        $eventClass = SomeDomainEvent::class;
        $payload = ['foo' => 'bar'];

        $serializer = new DefaultPayloadSerializer();

        $event = $serializer->unserializePayload($eventClass, $payload);

        $this->assertInstanceOf(SomeDomainEvent::class, $event);

        $this->assertEquals(['foo' => 'bar'], $event->toPayload());
    }
}
