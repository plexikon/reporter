<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Support\Publisher;

use Plexikon\Reporter\Manager\ReporterDriverManager;
use Plexikon\Reporter\Support\HasPromiseHandler;

class LazyPublisher
{
    use HasPromiseHandler;

    private ReporterDriverManager $publisherManager;
    private ?string $publisherName = null;

    public function __construct(ReporterDriverManager $publisherManager)
    {
        $this->publisherManager = $publisherManager;
    }

    public function publishCommand(object $command): void
    {
        $this->publisherManager->commandPublisher($this->publisherName)->dispatch($command);
        $this->publisherName = null;
    }

    public function publishEvent(object $event): void
    {
        $this->publisherManager->eventPublisher($this->publisherName)->dispatch($event);
        $this->publisherName = null;
    }

    public function publishQuery(object $query)
    {
        $promise = $this->publisherManager->queryPublisher($this->publisherName)->dispatch($query);
        $this->publisherName = null;

        return $this->handlePromise($promise);
    }

    public function withPublisherName(string $publisherName): self
    {
        $this->publisherName = $publisherName;

        return $this;
    }
}
