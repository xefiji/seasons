<?php

namespace Xefiji\Seasons\Query;


/**
 * Interface GroupQueryHandler
 * @package Xefiji\Seasons\Query
 */
interface GroupQueryHandler
{
    const HANDLER_METHOD_PREFIX = "handle";

    /**
     * @return array
     */
    public function listenTo();

    /**
     * @param Query $query
     * @return mixed
     */
    public function handle(Query $query);

    /**
     * @param Query $query
     * @return mixed
     */
    public function resolveHandlerMethod(Query $query);

}