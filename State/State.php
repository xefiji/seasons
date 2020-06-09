<?php


namespace Xefiji\Seasons\State;

/**
 * Class State
 *
 * Global wrapper for all state, acting like a state class in State Pattern, but allowing
 * more flexibility and various workflow configuration
 *
 * @package Xefiji\Seasons\State
 */
class State
{
    /**
     * @var object
     */
    private $context;

    /**
     * @var string
     */
    private $name;

    /**
     * State constructor.
     * @param string $name
     * @param null $context
     */
    public function __construct($name, $context = null)
    {
        $this->name = $name;
        $this->context = $context;
    }

    /**
     * @param $string
     * @param null $context
     * @return State
     */
    public static function fromString($string, $context = null)
    {
        return new self($string, $context);
    }

    /**
     * @param $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

}