<?php

namespace Xefiji\Seasons\Helper;

/**
 * Class Collection
 * @package Xefiji\Seasons\Helper
 */
class Collection
{
    /**
     * @param $collection
     * @return array
     */
    static function toIds($collection): array
    {
        $res = [];
        foreach ($collection as $item) {
            if (method_exists($item, 'getId')) {
                $res[] = $item->getId();
            }
        }

        return $res;
    }

    /**
     * @param array $initial
     * @param array $new
     * @return bool
     */
    static function differs(array $initial, array $new): bool
    {
        $countInitial = count($initial);
        $countNew = count($new);
        if ($countInitial !== $countNew) {
            return true;
        }

        $diff = array_diff($initial, $new);
        if (count($diff) !== 0) {
            return true;
        }

        return false;
    }
}