<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Extractor;

use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\Container\ContainerInterface;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\ExtractorInterface;
use Platine\Orm\RepositoryInterface;

/**
 * @class RepositoryExtractor
 * @package Platine\App\Module\Etl\Extractor
 */
class RepositoryExtractor implements ExtractorInterface
{
    /**
     * The container instance
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * The data definition fields
     * @var array<string, mixed>
     */
    protected array $dataFields = [];

    /**
     * The filters
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * The data definition instance
     * @var DataDefinition
     */
    protected DataDefinition $dataDefinition;

    /**
     * Create new instance
     * @param ContainerInterface $container
     * @param DataDefinition $dataDefinition
     * @param array<string, mixed> $dataFields
     * @param array<string, mixed> $filters
     */
    public function __construct(
        ContainerInterface $container,
        DataDefinition $dataDefinition,
        array $dataFields = [],
        array $filters = []
    ) {
        $this->container = $container;
        $this->dataDefinition = $dataDefinition;
        $this->dataFields = $dataFields;
        $this->filters = $filters;
    }

    /**
    * {@inheritdoc}
    */
    public function extract($input, Etl $etl, array $options = []): iterable
    {
        $fields = $this->dataFields;
        $definition = $this->dataDefinition;

        /** @var RepositoryInterface $repository */
        $repository = $this->container->get($definition->model);

        $results = $repository->query()
                              ->filter($this->filters)
                              ->all((array) array_combine(
                                  $fields['columns'] ?? [],
                                  $fields['fields'] ?? []
                              ), false);

        return $results;
    }
}
