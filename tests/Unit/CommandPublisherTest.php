<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Test\Unit;

use Plexikon\Reporter\CommandPublisher;
use Plexikon\Reporter\Message\Alias\ClassNameMessageAlias;
use Plexikon\Reporter\Message\Alias\DefaultMessageAlias;
use Plexikon\Reporter\Message\Decorator\AsyncMarkerMessageDecorator;
use Plexikon\Reporter\Message\Decorator\EventIdMessageDecorator;
use Plexikon\Reporter\Message\Decorator\EventTypeMessageDecorator;
use Plexikon\Reporter\Message\Decorator\MessageTypeMessageDecorator;
use Plexikon\Reporter\Message\Decorator\TimeOfRecordingMessageDecorator;
use Plexikon\Reporter\Message\Factory\PublisherMessageFactory;
use Plexikon\Reporter\Message\Producer\SyncMessageProducer;
use Plexikon\Reporter\Message\Serializer\DefaultMessageSerializer;
use Plexikon\Reporter\Message\Serializer\DefaultPayloadSerializer;
use Plexikon\Reporter\Publisher\Middleware\ChainMessageDecoratorMiddleware;
use Plexikon\Reporter\Publisher\Middleware\RoutableCommandMiddleware;
use Plexikon\Reporter\Publisher\Router\SingleHandlerRouter;
use Plexikon\Reporter\Support\Clock\ReporterClock;
use Plexikon\Reporter\Support\Message\ChainMessageDecorator;
use Plexikon\Reporter\Test\Mock\SomeCommand;
use Plexikon\Reporter\Test\TestCase;

class CommandPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function it_dispatch_command(): void
    {
        $command = SomeCommand::fromPayload(['foo' => 'bar']);
        $commandClass = SomeCommand::class;

        $messageHandled = false;
        $map = [$commandClass => function (SomeCommand $command) use (&$messageHandled): void {
            $messageHandled = true;
        }];

        $messageAlias = new ClassNameMessageAlias();
        $messageFactory = new PublisherMessageFactory(new DefaultMessageSerializer(
            $messageAlias, new DefaultPayloadSerializer()
        ));

        $router = new SingleHandlerRouter($map, $messageAlias);

        $routerMiddleware = new RoutableCommandMiddleware(
            $router, new SyncMessageProducer()
        );

        $messageDecoratorMiddleware = new ChainMessageDecoratorMiddleware(
            new ChainMessageDecorator(
                new EventIdMessageDecorator(),
                new EventTypeMessageDecorator($messageAlias),
                new MessageTypeMessageDecorator(),
                new TimeOfRecordingMessageDecorator(new ReporterClock()),
                new AsyncMarkerMessageDecorator()
            )
        );

        $publisher = new CommandPublisher($messageFactory, $messageDecoratorMiddleware, $routerMiddleware);
        $publisher->setPublisherName(CommandPublisher::class);
        $publisher->dispatch($command);

        $this->assertTrue($messageHandled);
    }

    /**
     * @test
     */
    public function it_dispatch_command_with_alias(): void
    {
        $command = SomeCommand::fromPayload(['foo' => 'bar']);

        $messageHandled = false;
        $map = ['some-command' => function (SomeCommand $command) use (&$messageHandled): void {
            $messageHandled = true;
        }];

        $messageAlias = new DefaultMessageAlias();
        $messageFactory = new PublisherMessageFactory(new DefaultMessageSerializer(
            $messageAlias, new DefaultPayloadSerializer()
        ));

        $router = new SingleHandlerRouter($map, $messageAlias);

        $routerMiddleware = new RoutableCommandMiddleware(
            $router, new SyncMessageProducer()
        );

        $messageDecoratorMiddleware = new ChainMessageDecoratorMiddleware(
            new ChainMessageDecorator(
                new EventIdMessageDecorator(),
                new EventTypeMessageDecorator($messageAlias),
                new MessageTypeMessageDecorator(),
                new TimeOfRecordingMessageDecorator(new ReporterClock()),
                new AsyncMarkerMessageDecorator()
            )
        );

        $publisher = new CommandPublisher($messageFactory, $messageDecoratorMiddleware, $routerMiddleware);

        $publisher->setPublisherName(CommandPublisher::class);

        $publisher->dispatch($command);

        $this->assertTrue($messageHandled);
    }
}
