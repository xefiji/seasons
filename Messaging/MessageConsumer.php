<?php

namespace Xefiji\Seasons\Messaging;

/**
 * Interface MessageConsumer
 * @package Xefiji\Seasons\Message
 */
interface MessageConsumer
{
    public function open($exchangeName);

    public function receive($exchangeName, $routingKey = null);

    public function close($exchangeName);

    public function callback($message);
}