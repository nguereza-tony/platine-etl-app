<?php

declare(strict_types=1);

namespace Platine\App\Model\Repository;

use Platine\App\Model\Entity\File;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
* @class FileRepository
* @package Platine\App\Model\Repository
*/
class FileRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, File::class);
    }
}
