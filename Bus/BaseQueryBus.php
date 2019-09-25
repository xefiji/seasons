<?php

namespace Xefiji\Seasons\Bus;

use Xefiji\Seasons\Query\GroupQueryHandler;
use Xefiji\Seasons\Query\Query;
use Xefiji\Seasons\Query\QueryHandler;

/**
 * Class BaseQueryBus
 * @package Xefiji\Seasons\Bus
 */
final class BaseQueryBus implements QueryBus
{
    /**
     * @var QueryHandler[]
     */
    private $queryHandlers = [];

    /**
     * @var Query[]
     */
    private $queue = [];

    /**
     * @var bool
     */
    private $isAsking = false;

    /**
     * @param Query $query
     * @return mixed
     */
    public function ask(Query $query)
    {
        $this->queue[] = $query;

        if (!$this->isAsking) {
            $this->isAsking = true;

            try {
                while ($query = array_shift($this->queue)) {
                    foreach ($this->queryHandlers as $handler) {

                        /*One query, one handler*/
                        if ($handler instanceof QueryHandler) {
                            if ($handler->listenTo() !== get_class($query)) {
                                continue;
                            }
                            return $handler->handle($query);
                        }

                        /*One handler, multiple query (throug a method resolver function)*/
                        if ($handler instanceof GroupQueryHandler) {
                            if (!in_array(get_class($query), $handler->listenTo())) {
                                continue;
                            }
                            return $handler->handle($query); //will resolve handler method name
                        }
                    }
                }
            } finally {
                $this->isAsking = false;
            }
        }
    }

    /**
     * @param QueryHandler $handler
     * @return mixed
     */
    public function subscribe(QueryHandler $handler)
    {
        $this->queryHandlers[] = $handler;
        return $this;
    }

    /**
     * @param GroupQueryHandler $handler
     * @return mixed
     */
    public function groupSubscribe(GroupQueryHandler $handler)
    {
        $this->queryHandlers[] = $handler;
        return $this;
    }
}
