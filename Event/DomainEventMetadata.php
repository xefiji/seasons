<?php

namespace Xefiji\Seasons\Event;


class DomainEventMetadata
{
    /**
     * @var string
     */
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

}