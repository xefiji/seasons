<?php

namespace Xefiji\Seasons;

use Xefiji\Seasons\Event\EventConflictException;
use Xefiji\Seasons\Exception\DomainLogicException;

/**
 * Class Retry
 * @package Xefiji\Seasons
 */
class Retry
{
    const CLASS_LOG_NAME = 'retryhelper';
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
     * @param $params
     * @param $maxTries
     * @param $sleep
     */
    private function __construct($callable = null, $object = null, $method = null, $params = [], $maxTries = 5, $sleep = 0)
    {
        $this->callable = $callable;
        $this->object = $object;
        $this->method = $method;
        $this->params = $params;
        $this->sleep = $sleep;
        if ($maxTries > self::TRIES_LIMIT) {
            throw new DomainLogicException(sprintf('Limit of %d tries cannot be exceeded', self::TRIES_LIMIT));
        }
        $this->maxTries = $maxTries;
    }

    /**
     * @param $object
     * @param $method
     * @param $params
     * @param $maxTries
     * @param $sleep
     * @return Retry
     */
    public static function obj($object, $method, $params = [], $maxTries = 5, $sleep = 0)
    {
        $self = new self($object, $method, $params, $maxTries, $sleep);
        $self->mode = self::OBJECT_MODE;
        return $self;
    }

    /**
     * @param callable $function
     * @param $maxTries
     * @param $sleep
     * @return Retry
     */
    public static function func(callable $function, $maxTries = 5, $sleep = 0)
    {
        $self = new self($function, null, null, [], $maxTries, $sleep);
        $self->mode = self::CALLABLE_MODE;
        return $self;
    }


    /**
     * @return mixed
     * @throws MaxTriesReachedException
     * @throws \Exception
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
                        throw new DomainLogicException("No mode specified");
                }

            } catch (EventConflictException $e) {
                $triesCount++;
                DomainLogger::instance()->error(sprintf("[%s] %s - retry %s/%s: %s", self::CLASS_LOG_NAME, __FUNCTION__, (string)$triesCount, (string)$this->maxTries, $e->getMessage()));
                sleep($this->sleep);
            } catch (\Exception $e) {
                DomainLogger::instance()->error(sprintf("[%s] %s - %s", self::CLASS_LOG_NAME, __FUNCTION__, $e->getMessage()));
                throw $e;
            }
        }

        throw new MaxTriesReachedException(sprintf('Maximum of %d tries reached', $this->maxTries));
    }
}