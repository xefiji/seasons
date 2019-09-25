<?php

namespace Xefiji\Seasons\Projection;


use Xefiji\Seasons\Aggregate\Aggregate;
use Xefiji\Seasons\Aggregate\AggregateId;

interface ReadModelRepository
{
    function addFromAggregate(Aggregate $aggregate, $joinedAggregates = []);

    function addFromAggregateId(AggregateId $aggregateId, $joinedAggregates = []);

    function findOneByAggregateId(AggregateId $aggregateId, $className);

    function findAllBy($className, $criterias);

    function findOneBy($className, $criterias);

    function findAll($className);

    function save(ReadModel $readModel);

    function remove(ReadModel $readModel);
}