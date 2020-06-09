<?php

namespace Xefiji\Seasons\Query;
use Xefiji\Seasons\Exception\DomainLogicException;

/**
 * Class AbstractGroupQueryHandler
 * @package Xefiji\Seasons\Query
 */
abstract class AbstractGroupQueryHandler
{
    /**
     * @param Query $query
     * @return mixed
     */
    public function handle(Query $query)
    {
        if ($method = $this->resolveHandlerMethod($query)) {
            return call_user_func([$this, $method], $query);
        }
        return null;
    }

    /**
     * @param Query $query
     * @return string
     * @throws \Exception
     */
    public function resolveHandlerMethod(Query $query)
    {
        $parts = explode("\\", get_class($query));
        $class = array_pop($parts);
        $method = GroupQueryHandler::HANDLER_METHOD_PREFIX . $class;
        if (method_exists($this, $method)) {
            return $method;
        }

        throw new DomainLogicException("Method {$method} not implemented in " . __CLASS__);
    }
}