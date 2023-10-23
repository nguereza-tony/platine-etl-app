<?php

declare(strict_types=1);

namespace Platine\App\Model\Entity;

use Platine\App\Helper\EntityHelper;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;

/**
* @class Enterprise
* @package Platine\App\Model\Entity
*/
class Enterprise extends Entity
{
    /**
    * {@inheritdoc}
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


         $mapper->filter('role', function (Query $q, $value) {
             $q->where('role_id')->is($value);
         });

         $mapper->filter('logo', function (Query $q, $value) {
             $q->where('logo_id')->is($value);
         });
    }
}
