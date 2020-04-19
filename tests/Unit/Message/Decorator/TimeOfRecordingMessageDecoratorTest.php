<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Decorator;

use DateTimeImmutable;
use DateTimeZone;
use Plexikon\Reporter\Contracts\Clock\Clock;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Decorator\TimeOfRecordingMessageDecorator;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Support\Clock\PointInTime;
use Plexikon\Reporter\Tests\Unit\TestCase;
use stdClass;

class TimeOfRecordingMessageDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_decorate_message_with_time_of_recording(): void
    {
        $event = new stdClass();

        $datetime = new DateTimeImmutable('now', new DateTimeZone('utc'));
        $pointInTime = PointInTime::fromDateTime($datetime);

        $clock = $this->prophesize(Clock::class);
        $clock->pointInTime()->willReturn($pointInTime)->shouldBeCalled();

        $message = new Message($event);

        $this->assertNull($message->header(MessageHeader::TIME_OF_RECORDING));

        $decorator = new TimeOfRecordingMessageDecorator($clock->reveal());

        $message = $decorator->decorate($message);

        $this->assertEquals(
            $datetime->format(PointInTime::DATE_TIME_FORMAT),
            $message->header(MessageHeader::TIME_OF_RECORDING)
        );
    }

    /**
     * @test
     */
    public function it_does_not_decorate_message_if_time_of_recording_already_exists(): void
    {
        $event = new stdClass();

        $clock = $this->prophesize(Clock::class);
        $clock->pointInTime()->shouldNotBeCalled();

        $message = new Message($event);

        $this->assertNull($message->header(MessageHeader::TIME_OF_RECORDING));

        $message = $message->withHeader(MessageHeader::TIME_OF_RECORDING, 'bar');

        $decorator = new TimeOfRecordingMessageDecorator($clock->reveal());

        $message = $decorator->decorate($message);

        $this->assertEquals(
            'bar',
            $message->header(MessageHeader::TIME_OF_RECORDING)
        );
    }
}
