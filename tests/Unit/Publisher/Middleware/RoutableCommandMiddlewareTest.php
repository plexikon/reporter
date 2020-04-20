<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Publisher\Middleware;

use Plexikon\Reporter\Contracts\Message\MessageProducer;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Middleware\RoutableCommandMiddleware;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\Unit\TestCase;

class RoutableCommandMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_handle_object_message_sync(): void
    {
        $message = new Message(new \stdClass());

        $messageHandled = false;
        $callback = function (\stdClass $object) use (&$messageHandled): void {
            $messageHandled = true;
        };

        $router = $this->prophesize(SingleHandlerRouter::class);
        $router->route($message)->willYield([$callback]);

        $producer = $this->prophesize(MessageProducer::class);
        $producer->mustBeHandledSync($message)->willReturn(true);

        $middleware = new RoutableCommandMiddleware($router->reveal(), $producer->reveal());
        $middleware($message, function () {
        });

        $this->assertTrue($messageHandled);
    }

    /**
     * @test
     */
    public function it_handle_message_sync_and_copy_headers_to_event(): void
    {
        $event = SomeDomainEvent::fromPayload([]);
        $message = new Message($event, ['foo' => 'bar']);

        $messageHandled = false;

        $callback = function (SomeDomainEvent $event) use (&$messageHandled): void {
            $messageHandled = true;
            $this->assertEquals(['foo' => 'bar'], $event->headers());
        };

        $router = $this->prophesize(SingleHandlerRouter::class);
        $router->route($message)->willYield([$callback]);

        $producer = $this->prophesize(MessageProducer::class);
        $producer->produce()->shouldNotBeCalled();
        $producer->mustBeHandledSync($message)->willReturn(true);

        $middleware = new RoutableCommandMiddleware($router->reveal(), $producer->reveal());
        $middleware($message, function () {});

        $this->assertTrue($messageHandled);
    }

    /**
     * @test
     */
    public function it_handle_message_async(): void
    {
        $message = new Message(new \stdClass());

        $router = $this->prophesize(SingleHandlerRouter::class);
        $router->route()->shouldNotBeCalled();

        $producer = $this->prophesize(MessageProducer::class);
        $producer->mustBeHandledSync($message)->willReturn(false)->shouldBeCalled();
        $producer->produce($message)->willReturn($message)->shouldBeCalled();

        $middleware = new RoutableCommandMiddleware($router->reveal(), $producer->reveal());
        $returnedMessage = $middleware($message, function (Message $message) {
            return $message;
        });

        $this->assertEquals($message, $returnedMessage);
    }
}
