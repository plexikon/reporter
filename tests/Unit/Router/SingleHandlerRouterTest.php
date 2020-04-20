<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Router;

use Illuminate\Contracts\Container\Container;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Exception\PublisherFailure;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainMessage;
use Plexikon\Reporter\Tests\TestDouble\SomeMessageHandler;
use Plexikon\Reporter\Tests\Unit\TestCase;

class SingleHandlerRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_yield_message_handler(): void
    {
        $messageHandled = false;
        $callback = function () use (&$messageHandled): void {
            $messageHandled = true;
        };

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => $callback];

        $container = $this->prophesize(Container::class);
        $container->get()->shouldNotBeCalled();

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $callableMethod = null;
        $message = new Message($event);

        $router = new SingleHandlerRouter($map, $alias->reveal(), $container->reveal(), $callableMethod);
        $messageHandler = $router->route($message)->current();
        $messageHandler();

        $this->assertTrue($messageHandled);
    }

    /**
     * @test
     */
    public function it_raise_exception_if_message_not_found_in_map(): void
    {
        $this->expectException(PublisherFailure::class);
        $this->expectErrorMessage('Unable to find message name foo in map');

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => function (): void {}];

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('foo')->shouldBeCalled();

        $router = new SingleHandlerRouter($map, $alias->reveal(), null, null);
        $router->route(new Message($event))->current();
    }

    /**
     * @test
     */
    public function it_raise_exception_with_many_message_handlers(): void
    {
        $this->expectException(PublisherFailure::class);

        $routerClass = SingleHandlerRouter::class;
        $this->expectErrorMessage("Router $routerClass support and require one handler only");

        $callback = function (): void {};

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => [$callback, $callback]];

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $router = new SingleHandlerRouter($map, $alias->reveal(), null, null);
        $router->route(new Message($event))->current();
    }

    /**
     * @test
     */
    public function it_raise_exception_with_no_message_handler(): void
    {
        $this->expectException(PublisherFailure::class);

        $routerClass = SingleHandlerRouter::class;
        $this->expectErrorMessage("Router $routerClass support and require one handler only");

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => []];

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $message = new Message($event);

        $router = new SingleHandlerRouter($map, $alias->reveal(), null, null);
        $router->route($message)->current();
    }

    /**
     * @test
     */
    public function it_resolve_string_message_handler_trough_ioc(): void
    {
        $callback = new SomeMessageHandler();

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => SomeMessageHandler::class];

        $container = $this->prophesize(Container::class);
        $container->make(SomeMessageHandler::class)->willReturn($callback);

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $router = new SingleHandlerRouter($map, $alias->reveal(), $container->reveal(), null);
        $messageHandler = $router->route(new Message($event))->current();
        $messageHandler();

        $this->assertTrue($callback->isMessageHandled());
    }

    /**
     * @test
     */
    public function it_bind_callable_method_to_not_callable_message_handler(): void
    {
        $callback = $this->validMessageHandler();

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => SomeMessageHandler::class];

        $container = $this->prophesize(Container::class);
        $container->make(SomeMessageHandler::class)->willReturn($callback);

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $message = new Message($event);

        $router = new SingleHandlerRouter($map, $alias->reveal(), $container->reveal(), 'someMethod');
        $messageHandler = $router->route($message)->current();
        $messageHandler();

        $this->assertTrue($callback->isMessageHandled());
    }

    /**
     * @test
     */
    public function it_raise_exception_if_string_message_handler_and_container_missing(): void
    {
        $this->expectException(PublisherFailure::class);
        $this->expectErrorMessage('Unable to resolve string message handler');

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => SomeMessageHandler::class];

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $callableMethod = null;
        $message = new Message($event);

        $router = new SingleHandlerRouter($map, $alias->reveal(), null, $callableMethod);
        $router->route($message)->current();
    }

    /**
     * @test
     */
    public function it_raise_exception_if_message_handler_is_not_callable(): void
    {
        $this->expectException(PublisherFailure::class);
        $this->expectErrorMessage('Unable to resolve message handler, got type: object');

        $callback = $this->invalidMessageHandler();

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => SomeMessageHandler::class];

        $container = $this->prophesize(Container::class);
        $container->make(SomeMessageHandler::class)->willReturn($callback);

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $callableMethod = null;
        $message = new Message($event);

        $router = new SingleHandlerRouter($map, $alias->reveal(), $container->reveal(), $callableMethod);
        $router->route($message)->current();
    }

    private function invalidMessageHandler(): object
    {
        return new class() {
        };
    }

    private function validMessageHandler(): object
    {
        return new class() {
            private bool $isMessageHandled = false;

            public function someMethod(): void
            {
                $this->isMessageHandled = true;
            }

            public function isMessageHandled(): bool
            {
                return $this->isMessageHandled;
            }
        };
    }
}
