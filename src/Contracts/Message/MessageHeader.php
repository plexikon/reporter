<?php

namespace Plexikon\Reporter\Contracts\Message;

interface MessageHeader
{
    const EVENT_ID = '__event_id';
    const EVENT_TYPE = '__event_type';
    const TIME_OF_RECORDING = '__time_of_recording';
    // const AGGREGATE_ROOT_ID = '__aggregate_root_id';
    // const AGGREGATE_ROOT_ID_TYPE = '__aggregate_root_id_type';
    // const AGGREGATE_ROOT_VERSION = '__aggregate_root_version';
    public const MESSAGE_TYPE = '__message_type';
    public const MESSAGE_BUS_TYPE = '__message_bus_type';
    public const MESSAGE_ASYNC_MARKED = '__message_async_marked';
}
