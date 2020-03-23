<?php

namespace Plexikon\Reporter\Contracts\Message;

interface MessageAlias
{
    public function classToType(string $eventClass): string;

    public function typeToClass(string $eventType): string;

    public function classToAlias(string $eventClass): string;

    public function instanceToType(object $instance): string;

    public function instanceToAlias(object $instance): string;
}
