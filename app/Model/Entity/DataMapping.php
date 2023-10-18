<?php

declare(strict_types=1);

namespace Platine\App\Model\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;
use Platine\Orm\Relation\ForeignKey;

/**
* @class DataMapping
* @package Platine\App\Model\Entity
* @extends Entity<DataMapping>
*/
class DataMapping extends Entity
{
    /**
    * @param EntityMapperInterface<DataMapping> $mapper
    * @return void
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->useTimestamp();
         $mapper->casts([
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);

         $mapper->relation('parent')->belongsTo(DataMapping::class, new ForeignKey([
            'id' => 'parent_id'
         ]));

         $mapper->filter('parent', function (Query $q, $value) {
             $q->where('parent_id')->is($value);
         });
    }
}
