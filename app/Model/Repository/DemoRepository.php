<?php

declare(strict_types=1);

namespace Platine\App\Model\Repository;

use Platine\App\Model\Entity\Demo;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
* @class DemoRepository
* @package Platine\App\Model\Repository
*/
class DemoRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, Demo::class);
    }
}
