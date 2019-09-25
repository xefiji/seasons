<?php

namespace Xefiji\Seasons\Bus;


use Xefiji\Seasons\Query\Query;
use Xefiji\Seasons\Query\QueryHandler;

/**
 * Interface QueryBus
 * @package Xefiji\Seasons\Bus
 */
interface QueryBus
{
    /**
     * @param Query $command
     * @return mixed
     */
    public function ask(Query $command);

    /**
     * @param QueryHandler $handler
     * @return mixed
     */
    public function subscribe(QueryHandler $handler);
}