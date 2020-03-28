<?php

namespace Plexikon\Reporter\Contracts\Publisher;

interface NamedPublisher extends Publisher
{
    public function setPublisherName(string $pubName): void;

    public function publisherName(): string;
}
