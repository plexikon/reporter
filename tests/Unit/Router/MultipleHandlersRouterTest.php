<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Router;

use Illuminate\Contracts\Container\Container;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Router\MultipleHandlersRouter;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainMessage;
use Plexikon\Reporter\Tests\Unit\TestCase;

class MultipleHandlersRouterTest extends TestCase
{
    /**
     * @test
     */
    public function it_allow_many_message_handlers(): void
    {
        $message1Handled = false;
        $message2Handled = false;

        $callback = function () use (&$message1Handled): void {
            $message1Handled = true;
        };

        $callback1 = function () use (&$message2Handled): void {
            $message2Handled = true;
        };

        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => [$callback, $callback1]];

        $container = $this->prophesize(Container::class);
        $container->get()->shouldNotBeCalled();

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $router = new MultipleHandlersRouter($map, $alias->reveal(), $container->reveal(), null);
        $messageHandlers = $router->route(new Message($event));

        foreach ($messageHandlers as $messageHandler) {
            $messageHandler();
        }

        $this->assertTrue($message1Handled);
        $this->assertTrue($message2Handled);
    }

    /**
     * @test
     */
    public function it_allow_no_message_handler(): void
    {
        $event = SomeDomainMessage::fromPayload([]);
        $map = ['some-domain-message' => []];

        $container = $this->prophesize(Container::class);
        $container->get()->shouldNotBeCalled();

        $alias = $this->prophesize(MessageAlias::class);
        $alias->instanceToAlias($event)->willReturn('some-domain-message')->shouldBeCalled();

        $callableMethod = null;
        $message = new Message($event);

        $router = new MultipleHandlersRouter($map, $alias->reveal(), $container->reveal(), $callableMethod);

        $this->assertNull($router->route($message)->current());
    }
}
