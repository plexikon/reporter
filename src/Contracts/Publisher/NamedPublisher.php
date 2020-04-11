<?php

namespace Plexikon\Reporter\Contracts\Publisher;

interface NamedPublisher extends Publisher
{
    /**
     * @param string $publisherName
     */
    public function setPublisherName(string $publisherName): void;

    /**
     * @return string
     */
    public function publisherName(): string;
}
