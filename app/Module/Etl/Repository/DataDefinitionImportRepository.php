<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Repository;

use Platine\App\Module\Etl\Entity\DataDefinitionImport;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
* @class DataDefinitionImportRepository
* @package Platine\App\Module\Etl\Repository
* @extends Repository<DataDefinitionImport>
*/
class DataDefinitionImportRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager<DataDefinitionImport> $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, DataDefinitionImport::class);
    }
}
