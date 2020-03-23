<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Alias;

use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Message\Message;
use function get_class;

final class ClassNameMessageAlias implements MessageAlias
{
    public function classToType(string $eventClass): string
    {
        return $eventClass;
    }

    public function typeToClass(string $eventType): string
    {
        return $eventType;
    }

    public function classToAlias(string $eventClass): string
    {
        return $eventClass;
    }

    public function instanceToType(object $instance): string
    {
        if ($instance instanceof Message) {
            $instance = $instance->event();
        }

        return get_class($instance);
    }

    public function instanceToAlias(object $instance): string
    {
        if ($instance instanceof Message) {
            $instance = $instance->event();
        }

        return get_class($instance);
    }
}
