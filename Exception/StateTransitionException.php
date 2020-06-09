<?php
namespace Xefiji\Seasons\Exception;


/**
 * Class StateTransitionException
 * @package Xefiji\Seasons\Exception
 */
class StateTransitionException extends DomainLogicException
{
    /**
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}