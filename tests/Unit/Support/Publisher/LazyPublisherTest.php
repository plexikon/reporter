<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Support\Publisher;

use Plexikon\Reporter\Contracts\Publisher\Publisher;
use Plexikon\Reporter\Manager\ReporterDriverManager;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Support\Publisher\LazyPublisher;
use Plexikon\Reporter\Tests\Unit\TestCase;
use React\Promise\Deferred;

class LazyPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_command_with_publisher_resolved_with_manager(): void
    {
        $message = new Message(new \stdClass());

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->commandPublisher(null)->willReturn($publisher->reveal());

        $lazyPublisher = new LazyPublisher($manager->reveal());
        $lazyPublisher->publishCommand($message);
    }

    /**
     * @test
     */
    public function it_dispatch_command_with_named_publisher_resolved_with_manager(): void
    {
        $message = new Message(new \stdClass());

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->commandPublisher('foo')->willReturn($publisher->reveal());

        $lazyPublisher = new LazyPublisher($manager->reveal());
        $lazyPublisher = $lazyPublisher->withPublisherName('foo');
        $lazyPublisher->publishCommand($message);
    }

    /**
     * @test
     */
    public function it_dispatch_event_with_publisher_resolved_with_manager(): void
    {
        $message = new Message(new \stdClass());

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->eventPublisher(null)->willReturn($publisher->reveal());

        $lazyPublisher = new LazyPublisher($manager->reveal());
        $lazyPublisher->publishEvent($message);
    }

    /**
     * @test
     */
    public function it_dispatch_event_with_named_publisher_resolved_with_manager(): void
    {
        $message = new Message(new \stdClass());

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->eventPublisher('foo')->willReturn($publisher->reveal());

        $lazyPublisher = new LazyPublisher($manager->reveal());
        $lazyPublisher = $lazyPublisher->withPublisherName('foo');
        $lazyPublisher->publishEvent($message);
    }

    /**
     * @test
     */
    public function it_dispatch_query_with_publisher_resolved_with_manager(): void
    {
        $message = new Message(new \stdClass());

        $deferred = new Deferred();
        $promise = $deferred->promise();

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->willReturn($promise)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->queryPublisher(null)->willReturn($publisher->reveal());

        $lazyPublisher = new LazyPublisher($manager->reveal());
        $result = $lazyPublisher->publishQuery($message);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_dispatch_query_with_named_publisher_resolved_with_manager(): void
    {
        $message = new Message(new \stdClass());

        $deferred = new Deferred();
        $promise = $deferred->promise();

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->willReturn($promise)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->queryPublisher('foo')->willReturn($publisher->reveal());

        $lazyPublisher = new LazyPublisher($manager->reveal());
        $lazyPublisher = $lazyPublisher->withPublisherName('foo');
        $result = $lazyPublisher->publishQuery($message);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_reset_command_named_publisher_on_multiple_dispatch(): void
    {
        $message = new Message(new \stdClass());

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->commandPublisher('foo')->willReturn($publisher->reveal())->shouldBeCalled();
        $manager->commandPublisher('bar')->willReturn($publisher->reveal())->shouldBeCalled();

        $lazyPublisher = new LazyPublisher($manager->reveal());

        $lazyPublisher = $lazyPublisher->withPublisherName('foo');
        $lazyPublisher->publishCommand($message);

        $lazyPublisher = $lazyPublisher->withPublisherName('bar');
        $lazyPublisher->publishCommand($message);
    }

    /**
     * @test
     */
    public function it_reset_event_named_publisher_on_multiple_dispatch(): void
    {
        $message = new Message(new \stdClass());

        $publisher = $this->prophesize(Publisher::class);
        $publisher->dispatch($message)->shouldBeCalled();

        $manager = $this->prophesize(ReporterDriverManager::class);
        $manager->eventPublisher('foo')->willReturn($publisher->reveal())->shouldBeCalled();
        $manager->eventPublisher('bar')->willReturn($publisher->reveal())->shouldBeCalled();

        $lazyPublisher = new LazyPublisher($manager->reveal());

        $lazyPublisher = $lazyPublisher->withPublisherName('foo');
        $lazyPublisher->publishEvent($message);

        $lazyPublisher = $lazyPublisher->withPublisherName('bar');
        $lazyPublisher->publishEvent($message);
    }
}
