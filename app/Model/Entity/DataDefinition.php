<?php

declare(strict_types=1);

namespace Platine\App\Model\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;

/**
* @class DataDefinition
* @package Platine\App\Model\Entity
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
