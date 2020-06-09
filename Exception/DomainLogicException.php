<?php

namespace Xefiji\Seasons\Exception;

/**
 * Use as is or extend to customize
 *
 * Class DomainLogicException
 * @package Xefiji\Seasons\Exception
 */
class DomainLogicException extends \Exception
{
    const HTTP_CODE = 400;

    /**
     * DomainLogicException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = "", $code = self::HTTP_CODE, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}