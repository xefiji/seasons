<?php

namespace Xefiji\Seasons\Event;


interface IDomainEvent
{
    public function aggregateId();

    public function createdAt();

    public function by();
}