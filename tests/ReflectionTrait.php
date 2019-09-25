<?php

namespace Xefiji\Seasons\tests;

/**
 * Class ReflectionTrait
 * @package Xefiji\Seasons\tests
 */
trait ReflectionTrait
{
    /**
     * @param $object
     * @param $property
     * @return mixed
     */
    public function getPrivatePropertyValue($object, $property)
    {
        $r = new \ReflectionClass($object);

        $r_property = $r->getProperty($property);
        $r_property->setAccessible(true);

        return $r_property->getValue($object);
    }
}