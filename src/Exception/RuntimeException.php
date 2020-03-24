<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Exception;

use Plexikon\Reporter\Contracts\Exception\PublisherException;

class RuntimeException extends \RuntimeException implements PublisherException
{
    //
}
