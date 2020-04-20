<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Producer;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Message\Producer\MessageJob;
use Plexikon\Reporter\Publisher\Publisher;
use Plexikon\Reporter\Tests\Unit\TestCase;

class MessageJobTest extends TestCase
{
    /**
     * @test
     */
    public function it_access_connection_from_public_property(): void
    {
        $job = new MessageJob(['foo'], 'bus_type', 'default', null);

        $this->assertEquals('default', $job->connection);
    }

    /**
     * @test
     */
    public function it_push_job_on_queue(): void
    {
        $job = new MessageJob(['foo'], 'bus_type', 'default', 'high');

        $queue = $this->prophesize(Queue::class);
        $queue->pushOn('high', $job)->shouldBeCalled();

        $job->queue($queue->reveal(), $job);
    }

    /**
     * @test
     */
    public function it_display_name_from_message_header(): void
    {
        $payload = [
            'headers' => [MessageHeader::EVENT_TYPE => 'foo_bar']
        ];

        $job = new MessageJob($payload, 'bus_type', 'default', 'high');

        $this->assertEquals('foo_bar', $job->displayName());
    }

    /**
     * @test
     */
    public function it_dispatch_message_to_publisher(): void
    {
        $payload = ['foo' => 'bar'];

        $job = new MessageJob($payload, 'bus_type', 'default', 'high');

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($payload)->shouldBeCalled();

        $container = $this->prophesize(Container::class);
        $container->get('bus_type')->willReturn($publisher->reveal());

        $job->handle($container->reveal());
    }
}
