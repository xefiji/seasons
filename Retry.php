<?php

namespace Xefiji\Seasons;

use Xefiji\Seasons\Event\EventConflictException;

/**
 * Class Retry
 * @package Xefiji\Seasons
 */
class Retry
{
    const TRIES_LIMIT = 20;
    const OBJECT_MODE = 1;
    const CALLABLE_MODE = 2;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var
     */
    private $object;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $params;

    /**
     * @var int
     */
    private $maxTries;

    /**
     * @var int
     */
    private $sleep;

    /**
     * Retry constructor.
     * @param null $callable
     * @param null $object
     * @param null $method
     * @param array $params
     * @param int $maxTries
     * @param int $sleep
     */
    private function __construct($callable = null, $object = null, $method = null, $params = [], $maxTries = 5, $sleep = 0)
    {
        $this->callable = $callable;
        $this->object = $object;
        $this->method = $method;
        $this->params = $params;
        $this->sleep = $sleep;
        if ($maxTries > self::TRIES_LIMIT) {
            throw new \LogicException(sprintf('Limit of %d tries cannot be exceeded', self::TRIES_LIMIT));
        }
        $this->maxTries = $maxTries;
    }

    /**
     * @param $object
     * @param $method
     * @param array $params
     * @param int $maxTries
     * @param int $sleep
     * @return Retry
     */
    public static function forObject($object, $method, $params = [], $maxTries = 5, $sleep = 0)
    {
        $self = new self($object, $method, $params, $maxTries, $sleep);
        $self->mode = self::OBJECT_MODE;
        return $self;
    }

    /**
     * @param callable $function
     * @param int $maxTries
     * @param int $sleep
     * @return Retry
     */
    public static function for (callable $function, $maxTries = 5, $sleep = 0)
    {
        $self = new self($function, null, null, [], $maxTries, $sleep);
        $self->mode = self::CALLABLE_MODE;
        return $self;
    }


    /**
     * @return mixed
     * @throws MaxTriesReachedException
     */
    public function run()
    {
        $triesCount = 0;
        while ($triesCount < $this->maxTries) {
            try {

                switch ($this->mode) {
                    case self::OBJECT_MODE:
                        $action = $this->method;
                        $object = $this->object;
                        $params = count($this->params) ? $this->params : null;
                        return call_user_func_array([$object, $action], $params);
                        break;
                    case self::CALLABLE_MODE:
                        $action = $this->callable;
                        return $action();
                        break;
                    default:
                        throw new \LogicException("No mode specified");
                }

            } catch (\Exception $e) {
                $triesCount++;
                DomainLogger::instance()->error("Retry {$triesCount}/{$this->maxTries}", [$e->getMessage()]);
                sleep($this->sleep);
            }
        }

        throw new MaxTriesReachedException(sprintf('Maximum of %d tries reached', $this->maxTries));
    }
}