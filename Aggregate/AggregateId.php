<?php

namespace Xefiji\Seasons\Aggregate;
use Xefiji\Seasons\Exception\DomainLogicException;

/**
 * Class AggregateId
 * @package Xefiji\Seasons
 */
abstract class AggregateId
{
    /**
     * @var string
     */
    protected $id;

    public function __construct()
    {
    }

    /**
     * @param $id
     * @return
     */
    abstract protected function isValid($id);

    /**
     * @return mixed
     */
    abstract protected function generate();


    /**
     * @return string
     */
    public function value()
    {
        return $this->id;
    }

    /**
     * @return static
     */
    public static function create()
    {
        $aggregateId = new static();
        $aggregateId->id = $aggregateId->generate();

        return $aggregateId;
    }

    /**
     * @param $var
     * @return string
     */
    protected function err($var)
    {
        return sprintf('<%s> can\'t handle the value <%s>.', static::class, is_scalar($var) ? $var : gettype($var));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value();
    }

    /**
     * @param $string
     * @param bool $strict
     * @return mixed
     * @throws DomainLogicException
     */
    public static function fromString($string, $strict = false)
    {
        $class = get_called_class();

        if ($strict && $string instanceof $class) { //don't allow switch
            throw new DomainLogicException("Param is already an instance of " . get_class($string));
        }

        if ($string instanceof $class) { //allow switch
            return $string;
        }

        $object = new $class();
        $object->isValid($string);
        $object->id = $string;

        return $object;
    }


    /**
     * @param $id
     * @param $idClass
     * @return AggregateId
     */
    public static function cast($id, $idClass)
    {
        if ($id instanceof $idClass) {
            return $id;
        }

        return forward_static_call([$idClass, "fromString"], $id);
    }
}