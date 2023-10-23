<?php

declare(strict_types=1);

namespace Platine\App\Module\Auth\Entity;

use Platine\App\Helper\EntityHelper;
use Platine\Framework\Auth\Entity\User as AppUser;
use Platine\Orm\Mapper\EntityMapperInterface;

/**
* @class User
* @package Platine\App\Module\Auth\Entity
*/
class User extends AppUser
{
    /**
    * {@inheritdoc}
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
        parent::mapEntity($mapper);

        /**
         * @var EntityHelper $entityHelper
         */
         $entityHelper = app(EntityHelper::class);
         $entityHelper->subscribeEvents($mapper);
    }
}
