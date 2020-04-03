<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Exception;

class Assertion extends \Assert\Assertion
{
    protected static $exceptionClass = InvalidArgumentException::class;
}
