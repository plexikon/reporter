<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Alias;

use Plexikon\Reporter\Message\Alias\DefaultMessageAlias;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\Unit\TestCase;

class DefaultMessageAliasTest extends TestCase
{
    private string $domainEventType = 'plexikon.reporter.tests.test_double.some_domain_event';
    private string $aliasEventType = 'some-domain-event';

    /**
     * @test
     */
    public function it_convert_class_to_type(): void
    {
        $alias = new DefaultMessageAlias();

        $this->assertEquals($this->domainEventType, $alias->classToType(SomeDomainEvent::class));
    }

    /**
     * @test
     */
    public function it_convert_class_to_alias(): void
    {
        $alias = new DefaultMessageAlias();

        $this->assertEquals($this->aliasEventType, $alias->classToAlias(SomeDomainEvent::class));
    }

    /**
     * @test
     */
    public function it_convert_type_to_class(): void
    {
        $alias = new DefaultMessageAlias();

        $this->assertEquals(SomeDomainEvent::class, $alias->typeToClass($this->domainEventType));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_type(): void
    {
        $alias = new DefaultMessageAlias();

        $this->assertEquals($this->domainEventType, $alias->instanceToType(SomeDomainEvent::fromPayload([])));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_type_from_message(): void
    {
        $alias = new DefaultMessageAlias();

        $message = new Message(SomeDomainEvent::fromPayload([]));

        $this->assertEquals($this->domainEventType, $alias->instanceToType($message));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_alias(): void
    {
        $alias = new DefaultMessageAlias();

        $this->assertEquals($this->aliasEventType, $alias->instanceToAlias(SomeDomainEvent::fromPayload([])));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_alias_from_message(): void
    {
        $alias = new DefaultMessageAlias();

        $message = new Message(SomeDomainEvent::fromPayload([]));

        $this->assertEquals($this->aliasEventType, $alias->instanceToAlias($message));
    }
}
