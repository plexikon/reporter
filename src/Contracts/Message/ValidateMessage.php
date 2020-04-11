<?php

namespace Plexikon\Reporter\Contracts\Message;

interface ValidateMessage extends Messaging
{
    /**
     * @return array
     */
    public function validationRules(): array;
}
