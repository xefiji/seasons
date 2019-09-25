<?php

namespace Xefiji\Seasons\Messaging;


/**
 * Interface LoggerInterface
 * @package Xefiji\Seasons\Message
 */
interface LoggerInterface
{
    /**
     * @param $message
     * @param array $context
     * @return mixed
     */
    public function error($message, $context = []);

    /**
     * @param $message
     * @param array $context
     * @return mixed
     */
    public function info($message, $context = []);
}