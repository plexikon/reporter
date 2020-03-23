<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Test\Unit\Message\Alias;

use Plexikon\Reporter\Message\Alias\DefaultMessageAlias;
use Plexikon\Reporter\Test\Mock\DummyEvent;
use Plexikon\Reporter\Test\TestCase;
use function get_class;

class DefaultMessageAliasTest extends TestCase
{
    /**
     * @test
     */
    public function it_convert_message(): void
    {
        $messageAlias = new DefaultMessageAlias();

        $instance = DummyEvent::fromPayload([]);
        $className = get_class($instance);
        $eventType = 'plexikon.reporter.test.mock.dummy_event';
        $eventAlias = 'dummy-event';

        $this->assertEquals($eventType, $messageAlias->classToType($className));

        $this->assertEquals($className, $messageAlias->typeToClass($eventType));

        $this->assertEquals($eventAlias, $messageAlias->instanceToAlias($instance));
    }
}
