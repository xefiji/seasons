<?php

namespace Xefiji\Seasons\Helper;
use Xefiji\Seasons\Exception\DomainLogicException;

/**
 * Class Date
 * @package Xefiji\Seasons\Helper
 */
final class Date
{
    const FORMAT_US = 'Y-m-d';
    const FORMAT_FR = 'd/m/Y';
    const DEFAULT_DATE = '1970-01-01';
    const DEFAULT_DATETIME_START = '1970-01-01 00:00:00';
    const DEFAULT_DATETIME_END = '1970-01-01 23:59:59';

    static $days = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    /**
     * @param $date
     * @return \DateTimeImmutable|null
     */
    static function cast($date): ?\DateTimeImmutable
    {
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($date);
        }

        if (is_string($date)) {
            return new \DateTimeImmutable($date);
        }

        return null;
    }

    /**
     * @param $first
     * @param $second
     * @param bool $cast
     * @param bool $withTime
     * @return bool
     * @throws DomainLogicException
     */
    static function differs($first, $second, $cast = true, $withTime = true): bool
    {
        if ($cast) {
            $msg = "One can compare only date objects";
            if (null == ($first = self::cast($first))) {
                throw new DomainLogicException($msg);
            }
            if (null == ($second = self::cast($second))) {
                throw new DomainLogicException($msg);
            }
        }

        if (!$withTime) {
            $first = $first->format(self::FORMAT_US);
            $second = $second->format(self::FORMAT_US);
        }

        return $first != $second;
    }

    /**
     * @param $first
     * @param $second
     * @param bool $cast
     * @param bool $withSeconds
     * @return bool
     * @throws DomainLogicException
     */
    static function timeDiffers($first, $second, $cast = true, $withSeconds = false): bool
    {
        if ($cast) {
            $msg = "One can compare only date objects";
            if (null == ($first = self::cast($first))) {
                throw new DomainLogicException($msg);
            }
            if (null == ($second = self::cast($second))) {
                throw new DomainLogicException($msg);
            }
        }

        $format = $withSeconds ? Time::FORMAT_W_SEC : Time::FORMAT_WO_SEC;
        return $first->format($format) != $second->format($format);
    }

    /**
     * @param $date
     * @return bool
     */
    static function isToday($date): bool
    {
        $createdAt = self::cast($date)->format(self::FORMAT_US);
        $today = self::cast(new \DateTime())->format(self::FORMAT_US);
        return $createdAt == $today;
    }

    /**
     * @param $date
     * @return \DateTimeImmutable|null
     * @throws DomainLogicException
     */
    public static function toDefault($date)
    {
        if ($date = self::cast($date)) { //ensure it's a date
            $dt = new \DateTime(); //to be able to set date and time
            $tmp = explode("-", self::DEFAULT_DATE);
            $dt->setTime($date->format('H'), $date->format('i'), $date->format('s'));
            $dt->setDate($tmp[0], $tmp[1], $tmp[2]);
            $clone = \DateTimeImmutable::createFromMutable($dt); //return an immutable
            return $clone;
        }

        throw new DomainLogicException(sprintf("Unable to parse date %s", gettype($date)));
    }


}