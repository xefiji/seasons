<?php

namespace Xefiji\Seasons\Exception;
use Xefiji\Seasons\Aggregate\AggregateId;

/**
 * Class AggregateNotFoundException
 * @package Xefiji\Seasons\Exception
 */
class AggregateNotFoundException extends DomainLogicException 
{
    /** @var string */
    protected $message = "Aggregate was not found";

    /**
     * AggregateNotFoundException constructor.
     * @param AggregateId|null $aggregateId
     * @param null $aggregateClass
     * @param null $code
     * @param \Exception|null $previous
     */
    public function __construct(AggregateId $aggregateId = null, $aggregateClass = null, $code = null, \Exception $previous = null)
    {
        if ($aggregateId) {
            $this->message = $this->message . " : " . $aggregateId->value();
        }

        if ($aggregateClass) {
            $this->message = $this->message . " : " . $aggregateClass;
        }

        parent::__construct($this->message, $code, $previous);
    }
}