<?php

namespace Xefiji\Seasons\Helper;

/**
 * Class OptionsResolver
 * @package Xefiji\Seasons
 */
class OptionsResolver
{
    /**
     * @var null|self
     */
    private static $instance = null;

    /**
     * @var array
     */
    private $options;

    /**
     * @return null|OptionsResolver
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->options;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->options[$key]);
    }

    /**
     * @throws \Exception
     */
    public function __clone()
    {
        throw new \Exception("Why would you clone a singleton ?");
    }

    /**
     * @throws \Exception
     */
    private function __wakeup()
    {
        throw new \Exception("Why would you unserialize a singleton ?");
    }
}