<?php

return [

    /**
     * Reporter clock
     * ---------------------------------------------
     *
     * in use in TimeOfRecordingMessageDecorator
     */
    'clock' => \Plexikon\Reporter\Support\Clock\ReporterClock::class,

    /**
     * Message
     * ---------------------------------------------
     */
    'message' => [

        /**
         * Transform array message and object to message in publisher
         */
        'factory' => \Plexikon\Reporter\Message\Factory\PublisherMessageFactory::class,

        /**
         * Serialize / Unserialize message
         */
        'serializer' => \Plexikon\Reporter\Message\Serializer\DefaultMessageSerializer::class,

        /**
         * Serialize / Unserialize payload
         */
        'payload_serializer' => \Plexikon\Reporter\Message\Serializer\DefaultPayloadSerializer::class,

        /**
         * Convert default class name to base class name
         */
        'alias' => \Plexikon\Reporter\Message\Alias\DefaultMessageAlias::class,

        /**
         * Add mandatory headers to message
         * @see \Plexikon\Reporter\Contracts\Message\MessageHeader
         */
        'decorator' => [
            \Plexikon\Reporter\Message\Decorator\EventIdMessageDecorator::class,
            \Plexikon\Reporter\Message\Decorator\EventTypeMessageDecorator::class,
            \Plexikon\Reporter\Message\Decorator\TimeOfRecordingMessageDecorator::class,
            \Plexikon\Reporter\Message\Decorator\AsyncMarkerMessageDecorator::class,
        ],

        /**
         * Message Producer
         * ---------------------------------------------
         *
         * Produce message sync/async depends on strategy
         */
        'producer' => [
            /**
             * Default can be override per publisher
             */
            'default' => 'sync',

            /**
             * Produce message async if message implement async contract
             *
             * @see \Plexikon\Reporter\Contracts\Message\AsyncMessage
             */
            'per_message' => [
                'queue' => null,
                'connection' => null,
            ],

            /**
             * Dispatch all messages through async publisher
             */
            'async_all' => [
                'queue' => null,
                'connection' => null,
            ]
        ],
    ],

    /**
     * Publisher middleware
     * ---------------------------------------------
     *
     * Merge with publisher decorators if exists
     */
    'middleware' => [
        \Plexikon\Reporter\Publisher\Middleware\PublisherExceptionMiddleware::class,
    ],

    /**
     * Publishers
     * ---------------------------------------------
     */
    'publisher' => [

        'command' => [
            'default' => [
                'route_strategy' => 'per_message',
                'handler_method' => 'command',
                'message' => [
                    'decorator' => []
                ],
                'middleware' => [
                    \Plexikon\Reporter\Publisher\Middleware\CommandValidationMiddleware::class,
                ],
                'map' => []
            ]
        ],

        'event' => [
            'default' => [
                'route_strategy' => 'per_message',
                'message' => [
                    'decorator' => []
                ],
                'handler_method' => 'onEvent',
                'middleware' => [],
                'map' => []
            ]
        ],

        'query' => [
            'default' => [
                'route_strategy' => 'sync',
                'message' => [
                    'decorator' => []
                ],
                'handler_method' => 'query',
                'middleware' => [],
                'map' => []
            ]
        ]
    ]
];
