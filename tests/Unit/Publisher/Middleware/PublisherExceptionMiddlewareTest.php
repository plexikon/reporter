<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Publisher\Middleware;

use Plexikon\Reporter\Exception\MessageDispatchedFailure;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Publisher\Middleware\PublisherExceptionMiddleware;
use Plexikon\Reporter\Tests\Unit\TestCase;

class PublisherExceptionMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_wrap_exception_thrown_during_dispatching(): void
    {
        $this->markTestIncomplete('test current message on message dispatched exception');

        $this->expectException(MessageDispatchedFailure::class);
        $this->expectErrorMessage('An error occurred while dispatching message. See previous exceptions');
        $this->expectExceptionCode(422);


        $message = new Message(new \stdClass());

        $middleware = new PublisherExceptionMiddleware();
        $middleware($message, function(): \Throwable{
            throw new \RuntimeException('foo');
        });

//        /** @var MessageDispatchedFailure $exception */
//        $exception =$this->getExpectedException();
//        $this->assertEquals($message, $exception->currentMessage());
    }
}
