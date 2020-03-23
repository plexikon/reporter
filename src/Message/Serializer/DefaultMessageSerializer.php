<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Serializer;

use Generator;
use Plexikon\Reporter\Contracts\Message\MessageAlias;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Contracts\Message\PayloadSerializer;
use Plexikon\Reporter\Contracts\Message\SerializablePayload;
use Plexikon\Reporter\Message\Message;

final class DefaultMessageSerializer implements MessageSerializer
{
    private MessageAlias $messageAlias;
    private PayloadSerializer $payloadSerializer;

    public function __construct(MessageAlias $messageAlias, PayloadSerializer $payloadSerializer)
    {
        $this->messageAlias = $messageAlias;
        $this->payloadSerializer = $payloadSerializer;
    }

    public function serializeMessage(Message $message): array
    {
        $event = $message->event();

        assert($event instanceof SerializablePayload);

        $payload = $this->payloadSerializer->serializePayload($event);

        return ['headers' => $message->headers(), 'payload' => $payload];
    }

    public function unserializePayload(array $payload): Generator
    {
        assert(isset($payload['headers'], $payload['payload']));

        $headers = $payload['headers'];
        $fromPayload = $payload['payload'];

        $className = $this->messageAlias->typeToClass($headers[MessageHeader::EVENT_TYPE]);

        $event = $this->payloadSerializer->unserializePayload($className, $fromPayload);

        yield new Message($event, $headers);
    }
}
