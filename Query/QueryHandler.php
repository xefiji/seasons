<?php

namespace Xefiji\Seasons\Query;


interface QueryHandler
{
    /**
     * @return string
     */
    public function listenTo();

    /**
     * @param Query $query
     * @return mixed
     */
    public function handle(Query $query);
}