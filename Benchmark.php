<?php

namespace Xefiji\Seasons;

/**
 * Class Benchmark
 * @package Xefiji\Seasons
 * @todo should do more stuff like convert in minutes, etc.
 */
class Benchmark
{
    private static $instance = null;
    private $start;
    private $end;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return array
     */
    public function start()
    {
        list($usec, $sec) = explode(' ', microtime());
        $this->start = $usec + $sec;
    }

    /**
     * @return array|float
     */
    public function end()
    {
        if (is_null($this->start)) {
            throw new \LogicException("Start should not be null");
        }

        list($usec, $sec) = explode(' ', microtime());
        $this->end = round(($usec + $sec) - $this->start, 4);
    }

    /**
     * @return mixed
     */
    public function toSeconds()
    {
        return $this->end;
    }

    /**
     * @return mixed
     */
    public function toMinutes()
    {
        if (is_null($this->end)) {
            throw new \LogicException("End should not be null");
        }

        $cents = round($this->end / 60, 2);
        $dec = intval(((($cents * 100) % 100) / 100) * 60);
        $res = intval($cents) + ($dec / 100);
        return number_format(round($res, 2), 2);
    }

}