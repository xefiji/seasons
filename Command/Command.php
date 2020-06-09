<?php

namespace Xefiji\Seasons\Command;

/**
 * Interface Command
 * @package Xefiji\Seasons
 *
 * "It’s a strictly defined message, it’s immutable.
 * The Command is not more than a Data Transfer Object which can be used by the Command Handler.
 * It represents the outside request structured in a well formalized way."
 *
 * "The relation between Command and Command Handler is 1:1.
 * A Command has only one Command Handler and vice versa."
 */
interface Command extends \Serializable
{

}