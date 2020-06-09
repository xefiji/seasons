<?php

namespace Xefiji\Seasons\Event;


interface IDomainEvent
{
    const ID = '_id';

    public function aggregateId();

    public function createdAt();

    public function by();
}