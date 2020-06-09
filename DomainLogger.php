<?php

namespace Xefiji\Seasons;

use Psr\Log\LoggerInterface;

/**
 * Class DomainLogger
 * @package Xefiji\Seasons
 * Singleton to allow multiple Logger tools as Monolog
 * Default: Syslog, the native PHP Logger
 */
class DomainLogger implements LoggerInterface
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    private static $instance = null;

    /**
     * DomainLogger constructor.
     */
    private function __construct()
    {
        $this->logger = null;
    }

    /**
     * Gets instance of the DomainLogger
     *
     * @return DomainLogger instance
     * @access public
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Override error method to use syslog in first and the correct one if a Logger is set
     *
     * @param $message
     * @param $context
     * @return bool
     */
    public function error($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_ERR, $message);
        } else {
            $this->logger->error($message, $context);
        }
    }

    /**
     * Just a wrapper to automate error messages formatting
     * @param $fqcn
     * @param $function
     * @param $message
     * @param array $context
     */
    public function errorf($message, $fqcn, $function, array $context = array())
    {
        $parts = explode("\\", $fqcn);
        $class = array_pop($parts);
        $className = strtolower($class);
        $string = sprintf("[%s] %s - %s", $className, $function, $message);
        $this->error($string, $context);
    }

    /**
     * Override info method to use syslog in first and the correct one if a Logger is set
     *
     * @param $message
     * @param $context
     * @return bool
     */
    public function info($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_INFO, $message);
        } else {
            $this->logger->info($message, $context);
        }
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_EMERG, $message);
        } else {
            $this->logger->emergency($message, $context);
        }
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_ALERT, $message);
        } else {
            $this->logger->alert($message, $context);
        }
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_CRIT, $message);
        } else {
            $this->logger->critical($message, $context);
        }
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_WARNING, $message);
        } else {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_NOTICE, $message);
        } else {
            $this->logger->notice($message, $context);
        }
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog(LOG_DEBUG, $message);
        } else {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if (is_null($this->logger)) {
            syslog($level, $message);
        } else {
            $this->logger->log($level, $message, $context);
        }
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