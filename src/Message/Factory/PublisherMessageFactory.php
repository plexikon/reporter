<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Message\Factory;

use Plexikon\Reporter\Contracts\Message\MessageFactory;
use Plexikon\Reporter\Contracts\Message\MessageSerializer;
use Plexikon\Reporter\Message\Message;

final class PublisherMessageFactory implements MessageFactory
{
    private MessageSerializer $messageSerializer;

    public function __construct(MessageSerializer $messageSerializer)
    {
        $this->messageSerializer = $messageSerializer;
    }

    public function createMessageFrom($message): Message
    {
        assert(!is_string($message), 'Dispatching string message is not allowed');

        if (is_array($message)) {
            $message = $this->messageSerializer->unserializePayload($message);
        }

        assert(is_object($message), 'Message can be an array, an object or an instance of ' . Message::class);

        if (!$message instanceof Message) {
            $message = new Message($message);
        }

        return $message;
    }
}
