<?php

namespace Xefiji\Seasons\Helper;


use Xefiji\Seasons\Exception\DomainLogicException;

final class Time
{
    const FORMAT_W_SEC = 'H:i:s';
    const FORMAT_WO_SEC = 'H:i';
    const DEFAULT_TIME = '00:00:00';
    const DEFAULT_TIME_END = '23:59:59';

    /**
     * @param $time
     * @return \DateTimeImmutable
     * @throws DomainLogicException
     */
    public static function toDateTime($time): \DateTimeImmutable
    {
        try {
            if (!($time instanceof \DateTimeInterface)) {
                $time = new \DateTimeImmutable(Date::DEFAULT_DATE . ' ' . $time);
            }

            if ($time->format(Date::FORMAT_US) !== Date::DEFAULT_DATE) {
                $time = new \DateTimeImmutable(Date::DEFAULT_DATE . ' ' . $time->format(self::FORMAT_W_SEC));
            }

        } catch (\Exception $e) {
            throw new DomainLogicException("Wrong time parameter: " . $e->getMessage());
        }

        return $time;
    }
}