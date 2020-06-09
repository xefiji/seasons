<?php

namespace Xefiji\Seasons\Messaging;


/**
 * Class PublishedMessage
 * @package Xefiji\Seasons\Message
 */
class PublishedMessage
{
    /**
     * @var string
     */
    private $trackerId;

    /**
     * @var string
     */
    private $mostRecentPublishedMessageId;

    /**
     * @var string
     */
    private $exchangeName;

    /**
     * PublishedMessage constructor.
     * @param $exchangeName
     * @param $mostRecentPublishedMessageId
     */
    public function __construct($exchangeName, $mostRecentPublishedMessageId)
    {
        $this->mostRecentPublishedMessageId = $mostRecentPublishedMessageId;
        $this->exchangeName = $exchangeName;
    }

    /**
     * @return string
     */
    public function mostRecentPublishedMessageId()
    {
        return (int)$this->mostRecentPublishedMessageId;
    }

    /**
     * @param $maxId
     */
    public function updateMostRecentPublishedMessageId($maxId)
    {
        $this->mostRecentPublishedMessageId = $maxId;
    }

    /**
     * @return string
     */
    public function trackerId()
    {
        return $this->trackerId;
    }

    /**
     * For APIS....
     */

    /**
     * @return string
     */
    public function getTrackerId(): string
    {
        return $this->trackerId;
    }

    /**
     * @return string
     */
    public function getMostRecentPublishedMessageId(): string
    {
        return $this->mostRecentPublishedMessageId;
    }

    /**
     * @return string
     */
    public function getExchangeName(): string
    {
        return $this->exchangeName;
    }
}