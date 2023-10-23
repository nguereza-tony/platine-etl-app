<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\Framework\Audit\Auditor;
use Platine\Framework\Audit\Enum\EventType;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\DataMapper;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Stdlib\Helper\Str;
use ReflectionClass;

/**
 * @class EntityHelper
 * @package Platine\App\Helper
 */
class EntityHelper
{
    public const NONE    = 0;
    public const DELETE    = 1;
    public const CREATE  = 2;
    public const UPDATE    = 4;
    public const ALL = 7;


    /**
     * The Auditor
     * @var Auditor
     */
    protected Auditor $auditor;

    /**
     * Create new instance
     * @param Auditor $auditor
     */
    public function __construct(Auditor $auditor)
    {
        $this->auditor = $auditor;
    }

    /**
     * Subscribe to entity event "save", "update", "delete"
     * @return void
     */
    public function subscribeEvents(EntityMapperInterface $mapper, int $type = self::ALL): void
    {
        $auditor = $this->auditor;

        if ($type & self::CREATE) {
            $mapper->on('save', function (Entity $entity, DataMapper $dm) use ($auditor) {
                $data = $entity->jsonSerialize();
                // TODO
                unset($data['password']);
                unset($data['created_at']);
                unset($data['updated_at']);
                $className = (new ReflectionClass($entity))->getShortName();

                $auditor->setDetail(sprintf(
                    'Create of %s %s',
                    $className,
                    Str::stringify($data)
                ))
                ->setEvent(EventType::CREATE)
                ->save();
            });
        }

        if ($type & self::UPDATE) {
            $mapper->on('update', function (Entity $entity, DataMapper $dm) use ($auditor) {
                $data = $entity->jsonSerialize();
               // TODO
                unset($data['password']);
                unset($data['created_at']);
                unset($data['updated_at']);
                $className = (new ReflectionClass($entity))->getShortName();

                $auditor->setDetail(sprintf(
                    'Update of %s %s',
                    $className,
                    Str::stringify($data)
                ))
                ->setEvent(EventType::UPDATE)
                ->save();
            });
        }

        if ($type & self::DELETE) {
            $mapper->on('delete', function (Entity $entity, DataMapper $dm) use ($auditor) {
                $data = $entity->jsonSerialize();
               // TODO
                unset($data['password']);
                unset($data['created_at']);
                unset($data['updated_at']);
                $className = (new ReflectionClass($entity))->getShortName();

                $auditor->setDetail(sprintf(
                    'Delete of %s %s',
                    $className,
                    Str::stringify($data)
                ))
                ->setEvent(EventType::DELETE)
                ->save();
            });
        }
    }
}
