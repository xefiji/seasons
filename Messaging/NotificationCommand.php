<?php

namespace Xefiji\Seasons\Messaging;


use Xefiji\Seasons\Aggregate\AggregateId;

interface NotificationCommand
{
    public function publish();
    public function publishFor(AggregateId $aggregateId);
    public function subscribe();
}