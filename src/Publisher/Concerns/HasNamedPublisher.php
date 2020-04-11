<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Concerns;

trait HasNamedPublisher
{
    private ?string $publisherName;

    public function setPublisherName(string $publisherName): void
    {
        $this->publisherName = $publisherName;
    }

    public function publisherName(): string
    {
        return $this->publisherName;
    }
}
