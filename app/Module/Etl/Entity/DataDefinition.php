<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Entity;

use Platine\App\Helper\EntityHelper;
use Platine\App\Model\Entity\Enterprise;
use Platine\App\Module\Auth\Entity\User;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;

/**
* @class DataDefinition
* @package Platine\App\Module\Etl\Entity
* @extends Entity<DataDefinition>
*/
class DataDefinition extends Entity
{
    /**
    * @param EntityMapperInterface<DataDefinition> $mapper
    * @return void
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->useTimestamp();
         $mapper->casts([
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);

         /**
         * @var EntityHelper $entityHelper
         */
         $entityHelper = app(EntityHelper::class);
         $entityHelper->subscribeEvents($mapper);

         $mapper->relation('enterprise')->belongsTo(Enterprise::class);
         $mapper->relation('user')->belongsTo(User::class);

         $mapper->filter('enterprise', function (Query $q, $value) {
             $q->where('enterprise_id')->is($value);
         });

         $mapper->filter('user', function (Query $q, $value) {
             $q->where('user_id')->is($value);
         });

         $mapper->filter('direction', function (Query $q, $value) {
             $q->where('direction')->is($value);
         });

         $mapper->filter('status', function (Query $q, $value) {
             $q->where('status')->is($value);
         });

         $mapper->filter('header', function (Query $q, $value) {
             $q->where('header')->is($value);
         });
    }
}
