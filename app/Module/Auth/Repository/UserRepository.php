<?php

declare(strict_types=1);

namespace Platine\App\Module\Auth\Repository;

use Platine\App\Module\Auth\Entity\User;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
* @class UserRepository
* @package Platine\App\Module\Auth\Repository
*/
class UserRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, User::class);
    }
}
