<?php

namespace Xefiji\Seasons\Helper;

/**
 * Class Date
 * @package Xefiji\Seasons\Helper
 */
final class Date
{
    const FORMAT_US = 'Y-m-d';
    const FORMAT_FR = 'd/m/Y';

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
     * @return bool
     */
    static function differs($first, $second, $cast = true):bool
    {
        if ($cast) {
            $msg = "One can compare only date objects";
            if (null == ($first = self::cast($first))) {
                throw new \LogicException($msg);
            }
            if (null == ($second = self::cast($second))) {
                throw new \LogicException($msg);
            }
        }

        return $first != $second;
    }

    /**
     * @param $date
     * @return bool
     */
    static function isToday($date)
    {
        $createdAt = self::cast($date)->format(self::FORMAT_US);
        $today = self::cast(new \DateTime())->format(self::FORMAT_US);
        return $createdAt == $today;
    }


}