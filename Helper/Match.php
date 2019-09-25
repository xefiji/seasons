<?php

namespace Xefiji\Seasons\Helper;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Xefiji\Seasons\IValueObject;

class Match
{
    /**
     * @param array ...$fields
     * @return bool
     */
    static function dateObjects(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!($field instanceof \DateTime) && !($field instanceof \DateTimeImmutable)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function serializedDate(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!($field instanceof \DateTime) && !($field instanceof \DateTimeImmutable)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function bools(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!is_bool($field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function serializedBools(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!is_bool($field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function valueObjects(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!($field instanceof IValueObject) && !method_exists($field, 'equals')) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function strings(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!is_string($field)) {
                return false;
            }
            //not json string
            if (self::jsonValid((string)$field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function integers(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!is_integer($field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function floats(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!is_float($field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function varchars(...$fields): bool
    {
        foreach ($fields as $field) {
            if (is_object($field)) {
                return false;
            }

            if (!is_string($field) && !is_integer($field) && !is_float($field)) {
                return false;
            }
            //not json string
            if (self::jsonValid((string)$field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function arrays(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!is_array($field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function arrayColls(...$fields): bool
    {
        foreach ($fields as $field) {
            if (!($field instanceof ArrayCollection) && !($field instanceof PersistentCollection)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function serializedObjects(...$fields): bool
    {
        $pattern = '#^{((\s*\\?["\']?\w+\\?["\']?\s*):(\\?["\']?\w+\\?["\']?\s*)(,?\s*))+}$#';
        foreach ($fields as $field) {
            if (!is_string($field)) {
                return false;
            }
            if (!preg_match($pattern, $field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function serializedArrays(...$fields): bool
    {
        $pattern = '#^\[(\s*\\?["\']?\w+\\?["\']?(,?\s*)(,?\s*))+\]$#';
        foreach ($fields as $field) {
            if (!is_string($field)) {
                return false;
            }
            if (!preg_match($pattern, $field)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array ...$fields
     * @return bool
     */
    static function jsonValid(...$fields)
    {
        foreach ($fields as $field) {
            $res = json_decode((string)$field);
            if (is_integer($res) || is_float($res)) {
                return false;
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $first
     * @param $second
     * @return bool
     */
    static function differs($first, $second):bool
    {
        return $first !== $second;
    }

    /**
     * @param $first
     * @param $second
     * @return bool
     */
    static function voDiffer(IValueObject $first, IValueObject $second):bool
    {
        return !$first->equals($second);
    }

    /**
     * @param $previous
     * @param $current
     * @return bool
     */
    static function hasChanged($previous, $current):bool
    {
        switch (true) {
            case self::valueObjects($previous):
                if (!$current->equals($previous)) {
                    return true;
                }
                break;
            case self::varchars($previous):
            case self::bools($previous):
                if (self::differs($previous, $current)) {
                    return true;
                }
                break;
            case self::dateObjects($previous):
                if (Date::differs($previous->format('Y-m-d'), $current->format('Y-m-d'))) {
                    return true;
                }
                break;
            case self::arrayColls($previous):
                if (Collection::differs($previous->toArray(), $current->toArray())) {
                    return true;
                }
                break;
            case self::arrays($previous):
                if (Collection::differs($previous, $current)) {
                    return true;
                }
                break;
            default:
                if (self::differs($previous, $current)) {
                    return true;
                }
                break;
        }
        return false;
    }

}