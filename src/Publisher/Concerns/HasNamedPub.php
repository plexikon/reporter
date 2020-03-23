<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Concerns;

trait HasNamedPub
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
