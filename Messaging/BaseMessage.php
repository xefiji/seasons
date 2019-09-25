<?php

namespace Xefiji\Seasons\Messaging;

/**
 * Class BaseMessage
 * Should be used to dispatch any content that might be spread among all apps through any message broker.
 * Don't use if this content is either a command or a domain event. Just use for non domain-oriented data.
 * @package Xefiji\Seasons\Messaging
 */
final class BaseMessage
{
    /**
     * @var string
     */
    private $content;

    /**
     * BaseMessage constructor.
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}