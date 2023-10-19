<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\App\Module\Auth\Entity\Permission;

/**
 * @class TreeHelper
 * @package Platine\App\Helper
 */
class TreeHelper
{
    /**
     * Create tree
     * @param array<mixed> $elements
     * @param mixed $parentId
     * @param mixed $parentId
     * @param string $idField
     * @param string $parentField
     * @return array<mixed>
     */
    public static function createTree(
        array $elements,
        $parentId = 0,
        string $idField = 'id',
        string $parentField = 'parent_id'
    ): array {
        $branch = [];

        foreach ($elements as $element) {
            if ($element[$parentField] == $parentId) {
                $children = self::createTree($elements, $element[$idField]);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * Return the permission tree
     * @param Permission[] $permissions
     * @param bool $js
     * @return array<int, mixed>
     */
    public static function getPermissionTree(array $permissions = [], bool $js = false): array
    {
        $all = [];
        foreach ($permissions as $l) {
            $per = [
                'id' => $l->id,
                'code' => $l->code,
                'description' => $l->description,
                'parent_id' => $l->parent_id,
            ];

            if ($js) {
                $per['text'] = $l->description;
                $per['key'] = $l->id;
            }

            $all[] = $per;
        }

        return self::createTree($all);
    }
}
