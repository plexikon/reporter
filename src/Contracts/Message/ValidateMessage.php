<?php

namespace Plexikon\Reporter\Contracts\Message;

interface ValidateMessage extends Messaging
{
    /**
     * Fetch laravel rules
     *
     * @return array
     */
    public function validationRules(): array;
}
