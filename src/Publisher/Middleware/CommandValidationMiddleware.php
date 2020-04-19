<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Publisher\Middleware;

use Illuminate\Contracts\Validation\Factory;
use Plexikon\Reporter\Contracts\Message\MessageHeader;
use Plexikon\Reporter\Contracts\Message\PreValidateMessage;
use Plexikon\Reporter\Contracts\Message\ValidateMessage;
use Plexikon\Reporter\Contracts\Publisher\Middleware;
use Plexikon\Reporter\Exception\Assertion;
use Plexikon\Reporter\Exception\ValidationMessageFailed;
use Plexikon\Reporter\Message\Message;

final class CommandValidationMiddleware implements Middleware
{
    private Factory $validator;

    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    public function __invoke(Message $message, callable $next)
    {
        if (!$message->isMessaging()) {
            return $next($message);
        }

        $event = $message->event();

        if ($event instanceof ValidateMessage) {
            $alreadyProducedAsync = $message->header(MessageHeader::MESSAGE_ASYNC_MARKED);

            Assertion::notNull($alreadyProducedAsync, 'Validate message require an async marker header');

            if ($alreadyProducedAsync || $event instanceof PreValidateMessage) {
                $this->validateMessage($message);
            }
        }

        return $next($message);
    }

    private function validateMessage(Message $message): void
    {
        $validator = $this->validator->make(
            $message->event()->toPayload(),
            $message->event()->validationRules()
        );

        if ($validator->fails()) {
            throw ValidationMessageFailed::withValidator($validator, $message);
        }
    }
}
