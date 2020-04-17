<?php

namespace Plexikon\Reporter\Contracts\Message;

interface Messaging extends SerializablePayload
{
    public const COMMAND = 'command';

    public const QUERY = 'query';

    public const EVENT = 'event';

    /**
     * @return string
     */
    public function messageType(): string;

    /**
     * @param array $headers
     * @return Messaging
     */
    public function withHeaders(array $headers): Messaging;

    /**
     * @return array
     */
    public function headers(): array;

    /**
     * @param string $header
     * @return mixed
     */
    public function header(string $header);
}
