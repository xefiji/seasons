<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Xefiji\Seasons\Aggregate\AggregateRepository;
use Xefiji\Seasons\Event\EventStore;

final class <?= $class_name ?> extends AggregateRepository
{
    public function __construct(EventStore $eventStore, SnapshotRepository $snapshotRepository)
    {
        parent::__construct($eventStore, $snapshotRepository);
    }

    public function find(<?= $aggregate_class_name ?>Id $<?= strtolower($aggregate_class_name) ?>Id): <?= $aggregate_class_name ?>
    {
        //snapshot load
        if ($this->hasSnapshotRepositorySetted() && $aggregate = $this->loadFromSnapShot($<?= strtolower($aggregate_class_name) ?>Id, <?= $aggregate_class_name ?>::class)) {
            return <?= $aggregate_class_name ?>::reconstitute($this->loadSincePlayhead($<?= strtolower($aggregate_class_name) ?>Id, $aggregate->playhead(), <?= $aggregate_class_name ?>::class), $aggregate);
        }

        //normal load
        return <?= $aggregate_class_name ?>::reconstitute($this->load($<?= strtolower($aggregate_class_name) ?>Id, <?= $aggregate_class_name ?>::class));
    }

    /*
     * Config example:
        App\Domain\Candidate\EventStoreCandidateRepository:
            class: 'App\Domain\Candidate\EventStoreCandidateRepository'
            arguments: ['@Xefiji\Seasons\Infrastructure\Doctrine\DoctrineEventStore', '@Xefiji\Seasons\Infrastructure\Doctrine\DoctrineSnapshotRepository']
            autowire: true
     */

}