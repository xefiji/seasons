<?php

namespace Xefiji\Seasons;

/**
 * Class ValueObject
 * @package Xefiji\Seasons
 * @todo compare on fields in childs, and guard with validation
 */
abstract class ValueObject
{
    const SEP = ' ';

    /**
     * @var string
     */
    protected $value;

    public function __construct()
    {
        $this->value = $this->normalize($this->getValue());
        $this->guard();
    }

    /**
     * @param ValueObject $toCompare
     * @return bool
     */
    public function equals(ValueObject $toCompare)
    {
        return $this->normalize($this->value) === $this->normalize($toCompare->getValue());
    }

    /**
     * Overwrite this if some specific normalization on string is needed in child class
     * @param $value
     * @return mixed
     */
    protected function normalize($value)
    {
        return utf8_encode((string)$value);
    }

    abstract public function getValue();

    abstract protected function guard();

    /**
     * To keep old values and update only not null
     * @param ValueObject $toMergeInThis
     * @return mixed
     */
    abstract public function merge(ValueObject $toMergeInThis);
}