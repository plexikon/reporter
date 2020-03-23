<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Support\Publisher;

use Plexikon\Reporter\Manager\PublisherManager;
use Plexikon\Reporter\Support\HasPromiseHandler;

class LazyPublisher
{
    use HasPromiseHandler;

    private PublisherManager $publisherManager;
    private ?string $publisherName = null;

    public function __construct(PublisherManager $publisherManager)
    {
        $this->publisherManager = $publisherManager;
    }

    public function command(object $command): void
    {
        $this->publisherManager->command($this->publisherName)->dispatch($command);
        $this->publisherName = null;
    }

    public function event(object $event): void
    {
        $this->publisherManager->event($this->publisherName)->dispatch($event);
        $this->publisherName = null;
    }

    public function query(object $query)
    {
        $promise = $this->publisherManager->query($this->publisherName)->dispatch($query);
        $this->publisherName = null;

        return $this->handlePromise($promise);
    }

    public function withPublisherName(string $publisherName): self
    {
        $this->publisherName = $publisherName;

        return $this;
    }
}
