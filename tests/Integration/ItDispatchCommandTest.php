<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Integration;

use Plexikon\Reporter\CommandPublisher;
use Plexikon\Reporter\Contracts\Publisher\Publisher;
use Plexikon\Reporter\Manager\ReporterServiceProvider;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\Integration\Traits\RegisterServicePublisher;
use Plexikon\Reporter\Tests\TestDouble\SomeCommand;

class ItDispatchCommandTest extends ITestCase
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
    }

    protected function getPackageProviders($app)
    {
        return [ReporterServiceProvider::class,];
    }

    /**
     * @test
     */
    public function it_dispatch_command(): void
    {
        /** @var Publisher $commandPublisher */
        $commandPublisher = $this->app->get(CommandPublisher::class);

        $command = SomeCommand::withData(['foo' => 'bar']);

        $commandPublisher->dispatch($command);

        $this->assertTrue($this->isMessageHandled);
    }
}
