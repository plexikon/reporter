<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Concerns;

trait HasNamedPublisher
{
    private ?string $pubName;

    public function setPublisherName(string $pubName): void
    {
        $this->pubName = $pubName;
    }

    public function publisherName(): string
    {
        return $this->pubName;
    }
}
