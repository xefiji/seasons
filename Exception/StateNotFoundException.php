<?php

namespace Xefiji\Seasons\Exception;


/**
 * Class StateNotFoundException
 * @package Xefiji\Seasons\Exception
 */
class StateNotFoundException extends DomainLogicException
{
    /**
     * @param string $message
     */
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}