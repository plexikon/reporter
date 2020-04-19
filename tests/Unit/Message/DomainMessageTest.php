<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Message;

use Plexikon\Reporter\Tests\TestDouble\SomeDomainMessage;
use Plexikon\Reporter\Tests\Unit\TestCase;

class DomainMessageTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_be_instantiated_with_payload(): void
    {
        $event = SomeDomainMessage::fromPayload(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $event->toPayload());
    }

    /**
     * @test
     */
    public function it_can_be_clone_with_headers(): void
    {
        $event = SomeDomainMessage::fromPayload(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $event->toPayload());

        $event = $event->withHeaders(['baz' => 'baz']);

        $this->assertEquals(['baz' => 'baz'] , $event->headers());
    }

    /**
     * @test
     */
    public function it_can_access_header_type(): void
    {
        $event = SomeDomainMessage::fromPayload(['foo' => 'bar']);

        $this->assertNull($event->header('foo'));

        $event = $event->withHeaders(['foo' => 'bar']);

        $this->assertEquals('bar', $event->header('foo'));
    }
}
