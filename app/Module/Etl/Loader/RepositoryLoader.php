<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Loader;

use Generator;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\Container\ContainerInterface;
use Platine\Database\Connection;
use Platine\Etl\Etl;
use Platine\Etl\Loader\LoaderInterface;
use Platine\Orm\RepositoryInterface;
use Platine\Stdlib\Helper\Arr;

/**
 * @class RepositoryLoader
 * @package Platine\App\Module\Etl\Loader
 */
class RepositoryLoader implements LoaderInterface
{
    /**
     * The data definition fields
     * @var array<string, mixed>
     */
    protected array $dataFields = [];

    /**
     * The data definition instance
     * @var DataDefinition
     */
    protected DataDefinition $dataDefinition;

    /**
     * The container instance
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * The Connection instance
     * @var Connection
     */
    protected Connection $connection;

    /**
     * Create new instance
     * @param DataDefinition $dataDefinition
     * @param ContainerInterface $container
     * @param Connection $connection
     * @param array<string, mixed> $dataFields
     */
    public function __construct(
        DataDefinition $dataDefinition,
        ContainerInterface $container,
        Connection $connection,
        array $dataFields
    ) {
        $this->dataFields = $dataFields;
        $this->dataDefinition = $dataDefinition;
        $this->container = $container;
        $this->connection = $connection;
    }


    /**
    * {@inheritdoc}
    */
    public function commit(bool $partial): void
    {
    }

    /**
    * {@inheritdoc}
    */
    public function init(array $options = []): void
    {
    }

    /**
    * {@inheritdoc}
    * @param Generator<int|string, mixed> $items
    */
    public function load(Generator $items, $key, Etl $etl): void
    {
        /** @var RepositoryInterface $repository */
        $repository = $this->container->get($this->dataDefinition->model);
        $columns = $this->dataFields['columns'] ?? [];
        $fieldInfos = $this->dataFields['data'] ?? [];

        foreach ($items as $item) {
            $data = $this->formatRow($item, $columns, $fieldInfos);

            $entity = $repository->create($data);
            $repository->save($entity);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function rollback(): void
    {
    }

    /**
     * For the entity row
     * @param array<string, mixed> $item
     * @param array<string> $columns
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, mixed>
     */
    protected function formatRow(array $item, array $columns, array $fields): array
    {

        /** @var array<string, mixed> $data */
        $data = Arr::only($item, $columns);
        $diffColumns = array_diff($columns, array_keys($data));
        foreach ($diffColumns as $col) {
            $field = $fields[$col];
            if ($field['default'] !== null) {
                $data[$col] = $field['default'];
            }
        }

        $result = [];
        foreach ($data as $k => $v) {
            $field = $fields[$k];
            $result[$field['column']] = $v;
        }

        return $result;
    }
}
