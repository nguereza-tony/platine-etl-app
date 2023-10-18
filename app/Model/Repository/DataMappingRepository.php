<?php

declare(strict_types=1);

namespace Platine\App\Model\Repository;

use Platine\Orm\EntityManager;
use Platine\Orm\Repository;
use Platine\App\Model\Entity\DataMapping;

/**
* @class DataMappingRepository
* @package Platine\App\Model\Repository
* @extends Repository<DataMapping>
*/
class DataMappingRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager<DataMapping> $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, DataMapping::class);
    }
}
