<?php

namespace Xefiji\Seasons\Messaging;

/**
 * Implementation can be of various forms:
 * - doctrine
 * - redis
 * - file
 * - whatever :)
 * Interface PublishedMessageTracker
 * @package Xefiji\Seasons\Message
 */
interface PublishedMessageTracker
{
    /**
     * @param $exchangeName
     * @return mixed
     */
    public function mostRecentPublishedMessageId($exchangeName);

    /**
     * @param $exchangeName
     * @param $notificationId
     * @return void
     */
    public function trackMostRecentPublishedMessage($exchangeName, $notificationId);
}