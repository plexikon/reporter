<?php

namespace Plexikon\Reporter\Contracts\Message;

interface MessageHeader
{
    public const EVENT_ID = '__event_id';

    public const EVENT_TYPE = '__event_type';

    public const TIME_OF_RECORDING = '__time_of_recording';

    public const MESSAGE_BUS_TYPE = '__message_bus_type';

    public const MESSAGE_ASYNC_MARKED = '__message_async_marked';
}
