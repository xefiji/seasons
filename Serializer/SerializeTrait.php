<?php

namespace Xefiji\Seasons\Serializer;


/**
 * Class SerializeTrait
 * @package Xefiji\Seasons\Serializer
 */
trait SerializeTrait
{
    /**
     * Warning: if this has objects, JMS might throw a exeption with
     * "Context visitingstack not working well". Be aware that all object fiels must be primitives types
     * Workaroung: override serialize in the class that uses this trait
     *
     * @return string
     */
    public function serialize()
    {
        return DomainSerializer::instance()->serialize($this);
    }

    /**
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        //...
    }
}