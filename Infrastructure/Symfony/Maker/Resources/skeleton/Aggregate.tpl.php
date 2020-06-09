<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Xefiji\Seasons\Aggregate\Aggregate;
use Xefiji\Seasons\Aggregate\AggregateCapability;

final class <?= $class_name ?> extends Aggregate
{
    use AggregateCapability;
}