<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Entity;

use Platine\App\Model\Entity\File;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;

/**
* @class DataDefinitionField
* @package Platine\App\Module\Etl\Entity
* @extends Entity<DataDefinitionImport>
*/
class DataDefinitionImport extends Entity
{
    /**
    * @param EntityMapperInterface<DataDefinitionImport> $mapper
    * @return void
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->useTimestamp();
         $mapper->casts([
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);

         $mapper->relation('definition')->belongsTo(DataDefinition::class);
         $mapper->relation('file')->belongsTo(File::class);

         $mapper->filter('status', function (Query $q, $value) {
             $q->where('status')->is($value);
         });

         $mapper->filter('file', function (Query $q, $value) {
             $q->where('file_id')->is($value);
         });

         $mapper->filter('definition', function (Query $q, $value) {
             $q->where('data_definition_id')->is($value);
         });
    }
}
