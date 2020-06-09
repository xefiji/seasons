<?php

namespace Xefiji\Seasons\Infrastructure;

use Xefiji\Seasons\Aggregate\AggregateId;
use Ramsey\Uuid\Uuid as RamseyUuid;


class Uuid extends AggregateId
{
    final protected function generate()
    {
        $uuid = RamseyUuid::uuid4()->toString();
        self::isValid($uuid);

        return $uuid;
    }


    final protected function isValid($id)
    {
        if (!RamseyUuid::isValid($id)) {
            throw new \InvalidArgumentException($this->err($id));
        }
    }
}