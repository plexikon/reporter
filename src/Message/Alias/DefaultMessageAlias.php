<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Alias;

use Illuminate\Support\Str;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Message\Message;
use function get_class;

final class DefaultMessageAlias implements MessageAlias
{
    public function classToType(string $eventClass): string
    {
        return str_replace('\\_', '.', Str::snake($eventClass));
    }

    public function typeToClass(string $eventType): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('.', '\\ ', $eventType))));
    }

    public function classToAlias(string $eventClass): string
    {
        $eventType = explode('.', $this->classToType($eventClass));

        return str_replace('_', '-', end($eventType));
    }

    public function instanceToType(object $instance): string
    {
        if ($instance instanceof Message) {
            $instance = $instance->event();
        }

        return $this->classToType(get_class($instance));
    }

    public function instanceToAlias(object $instance): string
    {
        if ($instance instanceof Message) {
            $instance = $instance->event();
        }

        return $this->classToAlias(get_class($instance));
    }
}
