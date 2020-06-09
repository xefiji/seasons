<?php


namespace Xefiji\Seasons\Helper;


class Strings
{
    /**
     * Transforms string to snake case
     * @param $string
     * @return string|string[]|null
     */
    public static function toSC($string)
    {
        $re = '/([a-z0-9])([A-Z])/m';
        return preg_replace_callback($re, function ($matches) {
            return $matches[1] . "_" . strtolower($matches[2]);
        }, $string);
    }
}