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
            'async' => [
                'abstract' => \Plexikon\Reporter\Message\Producer\AsyncMessageProducer::class,
                'strategy' => \Plexikon\Reporter\Contracts\Message\MessageProducer::ROUTE_PER_MESSAGE,
                'queue' => null,
                'connection' => null,
            ],
            'async_all' => [
                'abstract' => \Plexikon\Reporter\Message\Producer\AsyncMessageProducer::class,
                'strategy' => \Plexikon\Reporter\Contracts\Message\MessageProducer::ROUTE_ALL_ASYNC,
                'queue' => null,
                'connection' => null,
            ],
            'sync' => [
                'abstract' => \Plexikon\Reporter\Message\Producer\SyncMessageProducer::class,
            ]
        ],
    ],

    'middleware' => [
        \Plexikon\Reporter\Publisher\Middleware\PublisherExceptionMiddleware::class,
        \Plexikon\Reporter\Publisher\Middleware\DefaultChainMessageDecoratorMiddleware::class,
    ],

    'publisher' => [

        'command' => [
            'default' => [
                'alias' => null,
                'publisher' => \Plexikon\Reporter\CommandPublisher::class,
                'router' => \Plexikon\Reporter\Publisher\Middleware\RoutableCommandMiddleware::class,
                'route_strategy' => 'sync',
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
                'alias' => null,
                'publisher' => \Plexikon\Reporter\EventPublisher::class,
                'router' => \Plexikon\Reporter\Publisher\Middleware\RoutableEventMiddleware::class,
                'route_strategy' => 'sync',
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
                'alias' => null,
                'publisher' => \Plexikon\Reporter\QueryPublisher::class,
                'router' => \Plexikon\Reporter\Publisher\Middleware\RoutableQuerySyncMiddleware::class,
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
