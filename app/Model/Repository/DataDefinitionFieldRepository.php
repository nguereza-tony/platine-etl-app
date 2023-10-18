<?php

declare(strict_types=1);

namespace Platine\App\Model\Repository;

use Platine\Orm\EntityManager;
use Platine\Orm\Repository;
use Platine\App\Model\Entity\DataDefinitionField;

/**
* @class DataDefinitionFieldRepository
* @package Platine\App\Model\Repository
* @extends Repository<DataDefinitionField>
*/
class DataDefinitionFieldRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager<DataDefinitionField> $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, DataDefinitionField::class);
    }
}
