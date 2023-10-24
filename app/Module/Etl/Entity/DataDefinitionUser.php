<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Entity;

use Platine\App\Helper\EntityHelper;
use Platine\App\Module\Auth\Entity\User;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;

/**
* @class DataDefinitionUser
* @package Platine\App\Module\Etl\Entity
* @extends Entity<DataDefinitionUser>
*/
class DataDefinitionUser extends Entity
{
    /**
    * @param EntityMapperInterface<DataDefinitionUser> $mapper
    * @return void
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->table('data_definitions_users');

         /**
         * @var EntityHelper $entityHelper
         */
         $entityHelper = app(EntityHelper::class);
         $entityHelper->subscribeEvents($mapper);

         $mapper->relation('definition')->belongsTo(DataDefinition::class);
         $mapper->relation('user')->belongsTo(User::class);

         $mapper->filter('definition', function (Query $q, $value) {
             $q->where('data_definition_id')->is($value);
         });

         $mapper->filter('user', function (Query $q, $value) {
             $q->where('user_id')->is($value);
         });
    }
}
