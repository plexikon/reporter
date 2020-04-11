<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Support\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\Messaging;
use Plexikon\Reporter\Exception\RuntimeException;
use Plexikon\Reporter\Message\Message;
use Plexikon\Reporter\Support\Publisher\LazyPublisher;
use Symfony\Component\HttpFoundation\Response;

class HttpMessageMiddleware
{
    private LazyPublisher $publisher;
    private MessageFactory $messageFactory;
    private MessageAlias $messageAlias;

    public function __construct(LazyPublisher $messageBus,
                                MessageFactory $messageFactory,
                                MessageAlias $messageAlias)
    {
        $this->publisher = $messageBus;
        $this->messageFactory = $messageFactory;
        $this->messageAlias = $messageAlias;
    }

    public function handle(Request $request): JsonResponse
    {
        $event = $this->extractMessageFromRequest($request);

        return $this->buildResponse($this->dispatchMessage($event) ?? []);
    }

    private function dispatchMessage(Message $message): iterable
    {
        $event = $message->event();
        $messageType = $message->isMessaging() ? $event->messageType() : null;

        switch ($messageType) {
            case Messaging::COMMAND:
                $this->publisher->publishCommand($message);
                return [];
            case Messaging::EVENT:
                $this->publisher->publishEvent($message);
                return [];
            case Messaging::QUERY:
                return $this->publisher->publishQuery($message);
            default:
                throw new RuntimeException(
                    "Unable to detect message type from " . get_class($event),
                    Response::HTTP_BAD_REQUEST
                );
        }
    }

    private function extractMessageFromRequest(Request $request): Message
    {
        $message = $request->json()->all();
        $messageName = $this->detectMessageName($message);

        if (!isset($message['headers'])) {
            $message += ['headers' => []];
        }

        if (!isset($message['payload'])) {
            $message += ['payload' => []];
        }

        $message['headers'][MessageHeader::EVENT_TYPE] = $this->messageAlias->classToType($messageName);

        return $this->messageFactory->createMessageFrom($message);
    }

    private function detectMessageName(array $message): string
    {
        $messageName = $message['message_name'] ?? null;

        if (!is_string($messageName) || !class_exists($messageName) || !is_subclass_of($messageName, Messaging::class)) {
            throw new RuntimeException('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        return $messageName;
    }

    private function buildResponse(iterable $data, int $status = Response::HTTP_ACCEPTED): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}
