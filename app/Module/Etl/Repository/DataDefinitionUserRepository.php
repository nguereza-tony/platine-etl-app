<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Repository;

use Platine\App\Module\Etl\Entity\DataDefinitionUser;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
* @class DataDefinitionUserRepository
* @package Platine\App\Module\Etl\Repository
* @extends Repository<DataDefinitionUser>
*/
class DataDefinitionUserRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager<DataDefinitionUser> $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, DataDefinitionUser::class);
    }
}
