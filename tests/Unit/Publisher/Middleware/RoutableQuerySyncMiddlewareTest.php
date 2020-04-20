<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Publisher\Middleware;

use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Middleware\RoutableQuerySyncMiddleware;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use Plexikon\Reporter\Tests\Unit\TestCase;
use React\Promise\Deferred;
use RuntimeException;

class RoutableQuerySyncMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_handle_object_message_sync(): void
    {
        $message = new Message(new \stdClass());

        $callback = function (\stdClass $object, Deferred $promise) use (&$messageHandled): void {
            $promise->resolve(true);
        };

        $router = $this->prophesize(SingleHandlerRouter::class);
        $router->route($message)->willYield([$callback]);

        $middleware = new RoutableQuerySyncMiddleware($router->reveal());
        $promise = $middleware($message, function () {
        });

        $result = false;
        $promise->then(function ($r) use (&$result) {
            $result = $r;
        });

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_set_exception_caught_during_dispatching_in_promise(): void
    {
        $message = new Message(new \stdClass());

        $callback = function (\stdClass $object, Deferred $promise) use (&$messageHandled): void {
            throw new RuntimeException('foo');
        };

        $router = $this->prophesize(SingleHandlerRouter::class);
        $router->route($message)->willYield([$callback]);

        $middleware = new RoutableQuerySyncMiddleware($router->reveal());
        $promise = $middleware($message, function () {});

        $result = false;
        $exception = null;

        $promise->then(
            function ($r) use (&$result) {
                $result = $r;
            }, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertFalse($result);
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }
}
