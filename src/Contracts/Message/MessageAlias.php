<?php

namespace Plexikon\Reporter\Contracts\Message;

interface MessageAlias
{
    /**
     * @param string $eventClass
     * @return string
     */
    public function classToType(string $eventClass): string;

    /**
     * @param string $eventType
     * @return string
     */
    public function typeToClass(string $eventType): string;

    /**
     * @param string $eventClass
     * @return string
     */
    public function classToAlias(string $eventClass): string;

    /**
     * @param object $instance
     * @return string
     */
    public function instanceToType(object $instance): string;

    /**
     * @param object $instance
     * @return string
     */
    public function instanceToAlias(object $instance): string;
}
