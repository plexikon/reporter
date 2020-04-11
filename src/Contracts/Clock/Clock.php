<?php

namespace Plexikon\Reporter\Contracts\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Plexikon\Reporter\Support\Clock\PointInTime;

interface Clock
{
    /**
     * @return DateTimeImmutable
     */
    public function dateTime(): DateTimeImmutable;

    /**
     * @return PointInTime
     */
    public function pointInTime(): PointInTime;

    /**
     * @return DateTimeZone
     */
    public function timeZone(): DateTimeZone;
}
