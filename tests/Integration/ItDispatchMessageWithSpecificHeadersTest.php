<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Plexikon\Reporter\CommandPublisher;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Contracts\Publisher\Publisher;
use Plexikon\Reporter\Manager\ReporterServiceProvider;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Support\Clock\PointInTime;
use Plexikon\Reporter\Support\Clock\ReporterClock;
use Plexikon\Reporter\Tests\Integration\Traits\RegisterServicePublisher;
use Plexikon\Reporter\Tests\TestDouble\SomeCommand;

class ItDispatchMessageWithSpecificHeadersTest extends ITestCase
{
    use RegisterServicePublisher;

    private bool $isMessageHandled = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerPublishers();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('reporter.publisher.command.default.map', [
            'some-command' => function ($command): void {
                $this->isMessageHandled = true;

                /** @var SomeCommand $command */
                $this->assertInstanceOf(SomeCommand::class, $command);
                $this->assertEquals(['foo' => 'bar'], $command->toPayload());
            }
        ]);

        $middleware = $app['config']->get('reporter.middleware');

        $app['config']->set('reporter.middleware', array_merge($middleware, [$this->publisherMiddlewareSpy()]));
    }

    protected function getPackageProviders($app)
    {
        return [ReporterServiceProvider::class,];
    }

    /**
     * @test
     */
    public function it_dispatch_message_with_specific_headers(): void
    {
        $messageHeaders = [
            MessageHeader::EVENT_ID => '2d60a541-e6bc-4686-830e-bb6d772b4a62',
            MessageHeader::EVENT_TYPE => SomeCommand::class,
            MessageHeader::MESSAGE_ASYNC_MARKED => false,
            MessageHeader::TIME_OF_RECORDING => (new ReporterClock())->pointInTime(),
        ];

        /** @var Publisher $commandPublisher */
        $commandPublisher = $this->app->get(CommandPublisher::class);

        $command = SomeCommand::withData(['foo' => 'bar']);
        $message = new Message($command, $messageHeaders);

        $commandPublisher->dispatch($message);

        $this->assertTrue($this->isMessageHandled);
    }

    private function publisherMiddlewareSpy(): Middleware
    {
        $testCase = $this;

        return new class($testCase) implements Middleware {
            private TestCase $testCase;

            public function __construct(TestCase $testCase)
            {
                $this->testCase = $testCase;
            }

            public function __invoke(Message $message, callable $next)
            {
                $this->testCase->assertFalse($message->header(MessageHeader::MESSAGE_ASYNC_MARKED));

                $this->testCase->assertEquals(CommandPublisher::class, $message->header(MessageHeader::MESSAGE_BUS_TYPE));

                $this->testCase->assertEquals(SomeCommand::class, $message->header(MessageHeader::EVENT_TYPE));

                $this->testCase->assertEquals('2d60a541-e6bc-4686-830e-bb6d772b4a62', $message->header(MessageHeader::EVENT_ID));

                $this->testCase->assertInstanceOf(PointInTime::class, $message->header(MessageHeader::TIME_OF_RECORDING));

                return $next($message);
            }
        };
    }
}
