<?php

namespace Xefiji\Seasons;

use Psr\Log\LoggerInterface;

/**
 * Class DomainLogger
 * @package Xefiji\Seasons
 * Singleton to allow multiple Logger tools as Monolog
 * Default: Syslog, the native PHP Logger
 * @todo add else ? But isn't it useful to let it fail if no logger ?
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
        }

        $this->logger->error($message, $context);
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
        }

        $this->logger->info($message, $context);
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
        }

        $this->logger->emergency($message, $context);
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
        }

        $this->logger->alert($message, $context);
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
        }

        $this->logger->critical($message, $context);
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
        }

        $this->logger->warning($message, $context);
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
        }

        $this->logger->notice($message, $context);
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
        }

        $this->logger->debug($message, $context);
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
        }

        $this->logger->log($level, $message, $context);

    }
}