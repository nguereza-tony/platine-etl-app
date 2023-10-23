<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Entity;

use Platine\App\Helper\EntityHelper;
use Platine\App\Model\Entity\Enterprise;
use Platine\App\Module\Auth\Entity\User;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;
use Platine\Orm\Relation\ForeignKey;

/**
* @class DataDefinitionField
* @package Platine\App\Module\Etl\Entity
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

         /**
         * @var EntityHelper $entityHelper
         */
         $entityHelper = app(EntityHelper::class);
         $entityHelper->subscribeEvents($mapper);

         $mapper->relation('enterprise')->belongsTo(Enterprise::class);
         $mapper->relation('user')->belongsTo(User::class);
         $mapper->relation('definition')->belongsTo(DataDefinition::class);
         $mapper->relation('parent')->belongsTo(DataDefinitionField::class, new ForeignKey([
            'id' => 'parent_id'
         ]));

         $mapper->filter('enterprise', function (Query $q, $value) {
             $q->where('enterprise_id')->is($value);
         });

         $mapper->filter('user', function (Query $q, $value) {
             $q->where('user_id')->is($value);
         });

         $mapper->filter('parent', function (Query $q, $value) {
             $q->where('parent_id')->is($value);
         });

         $mapper->filter('definition', function (Query $q, $value) {
             $q->where('data_definition_id')->is($value);
         });
    }
}
