<?php

declare(strict_types=1);

namespace Platine\App\Model\Repository;

use Platine\Orm\EntityManager;
use Platine\Orm\Repository;
use Platine\App\Model\Entity\DataDefinition;

/**
* @class DataDefinitionRepository
* @package Platine\App\Model\Repository
* @extends Repository<DataDefinition>
*/
class DataDefinitionRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager<DataDefinition> $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, DataDefinition::class);
    }
}
