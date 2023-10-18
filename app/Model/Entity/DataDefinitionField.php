<?php

declare(strict_types=1);

namespace Platine\App\Model\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;
use Platine\Orm\Relation\ForeignKey;

/**
* @class DataDefinitionField
* @package Platine\App\Model\Entity
* @extends Entity<DataDefinitionField>
*/
class DataDefinitionField extends Entity
{
    /**
    * @param EntityMapperInterface<DataDefinitionField> $mapper
    * @return void
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->useTimestamp();
         $mapper->casts([
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);

         $mapper->relation('data_definition')->belongsTo(DataDefinition::class);
         $mapper->relation('data_mapping')->belongsTo(DataMapping::class);
         $mapper->relation('parent')->belongsTo(DataDefinitionField::class, new ForeignKey([
            'id' => 'parent_id'
         ]));

         $mapper->filter('parent', function (Query $q, $value) {
             $q->where('parent_id')->is($value);
         });

         $mapper->filter('data_definition', function (Query $q, $value) {
             $q->where('data_definition_id')->is($value);
         });

         $mapper->filter('data_mapping', function (Query $q, $value) {
             $q->where('data_mapping_id')->is($value);
         });
    }
}
