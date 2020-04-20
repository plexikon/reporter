<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message\Alias;

use Plexikon\Reporter\Message\Alias\ClassNameMessageAlias;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Tests\TestDouble\SomeDomainEvent;
use Plexikon\Reporter\Tests\Unit\TestCase;

class ClassNameMessageAliasTest extends TestCase
{
    private string $domainEvent = SomeDomainEvent::class;

    /**
     * @test
     */
    public function it_convert_class_to_type(): void
    {
        $alias = new ClassNameMessageAlias();

        $this->assertEquals($this->domainEvent, $alias->classToType(SomeDomainEvent::class));
    }

    /**
     * @test
     */
    public function it_convert_class_to_alias(): void
    {
        $alias = new ClassNameMessageAlias();

        $this->assertEquals($this->domainEvent, $alias->classToAlias(SomeDomainEvent::class));
    }

    /**
     * @test
     */
    public function it_convert_type_to_class(): void
    {
        $alias = new ClassNameMessageAlias();

        $this->assertEquals(SomeDomainEvent::class, $alias->typeToClass($this->domainEvent));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_type(): void
    {
        $alias = new ClassNameMessageAlias();

        $this->assertEquals($this->domainEvent, $alias->instanceToType(SomeDomainEvent::fromPayload([])));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_type_from_message(): void
    {
        $alias = new ClassNameMessageAlias();

        $message = new Message(SomeDomainEvent::fromPayload([]));

        $this->assertEquals($this->domainEvent, $alias->instanceToType($message));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_alias(): void
    {
        $alias = new ClassNameMessageAlias();

        $this->assertEquals($this->domainEvent, $alias->instanceToAlias(SomeDomainEvent::fromPayload([])));
    }

    /**
     * @test
     */
    public function it_convert_instance_to_alias_from_message(): void
    {
        $alias = new ClassNameMessageAlias();

        $message = new Message(SomeDomainEvent::fromPayload([]));

        $this->assertEquals($this->domainEvent, $alias->instanceToAlias($message));
    }
}
