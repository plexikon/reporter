<?php
declare(strict_types=1);

namespace Plexikon\Reporter\Support\Clock;

use DateTimeImmutable;

final class PointInTime
{
    /**
     * @private
     */
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s.uO';

    private DateTimeImmutable $pointInTime;

    private function __construct(DateTimeImmutable $pointInTime)
    {
        $this->pointInTime = $pointInTime;
    }

    public function dateTime(): DateTimeImmutable
    {
        return $this->pointInTime;
    }

    public function __toString(): string
    {
        return $this->pointInTime->format(self::DATE_TIME_FORMAT);
    }

    public function toString(): string
    {
        return $this->pointInTime->format(self::DATE_TIME_FORMAT);
    }

    public static function fromString(string $pointInTime): PointInTime
    {
        $dateTime = DateTimeImmutable::createFromFormat(self::DATE_TIME_FORMAT, $pointInTime);

        assert($dateTime instanceof DateTimeImmutable,'Invalid date time');

        return new PointInTime($dateTime);
    }

    public static function fromDateTime(DateTimeImmutable $dateTime): PointInTime
    {
        return new PointInTime($dateTime);
    }
}
