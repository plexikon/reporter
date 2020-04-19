<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Tests\Unit\Publisher\Concerns;

use Plexikon\Reporter\Publisher\Concerns\HasNamedPublisher;
use Plexikon\Reporter\Tests\Unit\TestCase;

class SomeNamedPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function it_access_publisher_name(): void
    {
        $instance = $this->someNamedPublisherInstance();

        $instance->setPublisherName('foo');

        $this->assertEquals('foo', $instance->publisherName());
    }

    /**
     * @test
     */
    public function it_raise_exception_if_access_property_before_initialization(): void
    {
        $this->expectException(\Error::class);
        $this->expectErrorMessage('Typed property');

        $instance = $this->someNamedPublisherInstance();

        $instance->publisherName();
    }

    private function someNamedPublisherInstance(): object
    {
        return new class() {
            use HasNamedPublisher;
        };
    }
}
