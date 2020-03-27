<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Support\Publisher;

use Plexikon\Reporter\Manager\PublisherServiceManager;
use Plexikon\Reporter\Support\HasPromiseHandler;

class LazyPublisher
{
    use HasPromiseHandler;

    private PublisherServiceManager $publisherManager;
    private ?string $publisherName = null;

    public function __construct(PublisherServiceManager $publisherManager)
    {
        $this->publisherManager = $publisherManager;
    }

    public function command(object $command): void
    {
        $this->publisherManager->createCommandPublisher($this->publisherName)->dispatch($command);
        $this->publisherName = null;
    }

    public function event(object $event): void
    {
        $this->publisherManager->createEventPublisher($this->publisherName)->dispatch($event);
        $this->publisherName = null;
    }

    public function query(object $query)
    {
        $promise = $this->publisherManager->createQueryPublisher($this->publisherName)->dispatch($query);
        $this->publisherName = null;

        return $this->handlePromise($promise);
    }

    public function withPublisherName(string $publisherName): self
    {
        $this->publisherName = $publisherName;

        return $this;
    }
}
