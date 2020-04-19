<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Factory;

use Generator;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Exception\InvalidArgumentException;
use Plexikon\Reporter\Message\Factory\PublisherMessageFactory;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\Unit\TestCase;
use stdClass;

class PublisherMessageFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_convert_dispatched_array_message_to_event_message(): void
    {
        $serializer = $this->prophesize(MessageSerializer::class);
        $serializer->unserializePayload(['foo' => 'bar'])
            ->willReturn($this->generateEvent())
            ->shouldBeCalled();

        $factory = new PublisherMessageFactory($serializer->reveal());

        $message = $factory->createMessageFrom(['foo' => 'bar']);

        $this->assertEquals(new stdClass(), $message->event());
    }

    /**
     * @test
     * @dataProvider provideInvalidEvent
     * @param $invalidEvent
     */
    public function it_raise_exception_if_message_is_invalid_type($invalidEvent): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Message can be an array, an object or an instance of ' . Message::class);

        $serializer = $this->prophesize(MessageSerializer::class)->reveal();
        $factory = new PublisherMessageFactory($serializer);

        $factory->createMessageFrom($invalidEvent);
    }

    /**
     * @test
     */
    public function it_convert_dispatched_event_to_message(): void
    {
        $serializer = $this->prophesize(MessageSerializer::class)->reveal();
        $factory = new PublisherMessageFactory($serializer);

        $message = $factory->createMessageFrom($event = SomeDomainEvent::withData(['foo' => 'bar']));

        $this->assertEquals($event, $message->event());
    }

    public function provideInvalidEvent(): Generator
    {
        yield [null];
        yield [5];
        yield ['foo'];
    }

    private function generateEvent(): Generator
    {
        yield new stdClass();
    }
}
