<?php

namespace Plexikon\Reporter\Contracts\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Plexikon\Reporter\Support\Clock\PointInTime;

interface Clock
{
    public function dateTime(): DateTimeImmutable;

    public function pointInTime(): PointInTime;

    public function timeZone(): DateTimeZone;
}
