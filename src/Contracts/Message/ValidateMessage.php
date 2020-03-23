<?php

namespace Plexikon\Reporter\Contracts\Message;

interface ValidateMessage
{
    /**
     * Fetch laravel rules
     *
     * @return array
     */
    public function validationRules(): array;
}
