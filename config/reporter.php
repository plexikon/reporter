<?php

return [

    'message' => [
        'factory' => \Plexikon\Reporter\Message\Factory\PublisherMessageFactory::class,

        'serializer' => \Plexikon\Reporter\Message\Serializer\DefaultMessageSerializer::class,

        'payload_serializer' => \Plexikon\Reporter\Message\Serializer\DefaultPayloadSerializer::class,

        'alias' => \Plexikon\Reporter\Message\Alias\DefaultMessageAlias::class,

        'decorator' => [
            \Plexikon\Reporter\Message\Decorator\EventIdMessageDecorator::class,
            \Plexikon\Reporter\Message\Decorator\EventTypeMessageDecorator::class,
            \Plexikon\Reporter\Message\Decorator\TimeOfRecordingMessageDecorator::class,
            \Plexikon\Reporter\Message\Decorator\MessageTypeMessageDecorator::class,
            \Plexikon\Reporter\Message\Decorator\AsyncMarkerMessageDecorator::class,
        ],

        'producer' => [
            'default' => 'sync', // default is overridden per publisher producer if exists

            'per_message' => [
                'queue' => null,
                'connection' => null,
            ],

            'async_all' => [
                'queue' => null,
                'connection' => null,
            ]
        ],
    ],

    'middleware' => [
        \Plexikon\Reporter\Publisher\Middleware\PublisherExceptionMiddleware::class,
    ],

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
                'route_strategy' => 'per_message',
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
