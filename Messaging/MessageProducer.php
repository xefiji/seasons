<?php

namespace Xefiji\Seasons\Messaging;

/**
 * Interface MessageProducer
 * @package Xefiji\Seasons\Message
 */
interface MessageProducer
{
    /**
     * Mainly used in a rmq like message broker configuration
     * @param $exchangeName
     * @return mixed
     */
    public function open($exchangeName);

    /**
     * Mainly used in a rmq like message broker configuration
     * @param $notif
     * @param null $exchangeName
     * @param null $notifType
     * @param null $notifId
     * @param \DateTimeImmutable|null $occuredOn
     * @return mixed
     */
    public function send($notif, $exchangeName = null, $notifType = null, $notifId = null, \DateTimeImmutable $occuredOn = null);


    /**
     * Mainly used in a rmq like message broker configuration
     * @param $exchangeName
     * @return mixed
     */
    public function close($exchangeName);

    /**
     * Depending on the transport choosen by the app, the notif must or not be serialized.
     * Do this below
     * @return mixed
     */
    public function serialize(): string;
}