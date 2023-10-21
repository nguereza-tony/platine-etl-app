<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\App\Model\Entity\DataDefinition;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\Container\ContainerInterface;
use Platine\Database\QueryBuilder;
use Platine\Etl\EtlTool;
use Platine\Stdlib\Helper\Str;

/**
 * @class DataDefinitionHelper
 * @package Platine\App\Helper
 */
class DataDefinitionHelper
{
    /**
     * The Container instance
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * The QueryBuilder instance
     * @var QueryBuilder
     */
    protected QueryBuilder $queryBuilder;

    /**
     * The DataDefinitionRepository
     * @var DataDefinitionRepository
     */
    protected DataDefinitionRepository $dataDefinitionRepository;

    /**
     * The DataDefinitionFieldRepository instance
     * @var DataDefinitionFieldRepository
     */
    protected DataDefinitionFieldRepository $dataDefinitionFieldRepository;

    /**
     * Create new instance
     * @param ContainerInterface $container
     * @param QueryBuilder $queryBuilder
     * @param DataDefinitionRepository $dataDefinitionRepository
     * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
     */
    public function __construct(
        ContainerInterface $container,
        QueryBuilder $queryBuilder,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository
    ) {
        $this->container = $container;
        $this->queryBuilder = $queryBuilder;
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
    }

    /**
     * Export the given definition
     * @param DataDefinition $definition
     * @param string $path
     * @param array<string, mixed> $filters
     * @return string the full file path
     */
    public function export(DataDefinition $definition, string $path, array $filters = []): string
    {
        $dataFields = $this->getDefinitionFields($definition->id);

        $filename = sprintf(
            '%s_%s.%s',
            Str::snake($definition->name),
            date('YmdH'),
            $definition->extension
        );
        $exportPath = $path . DIRECTORY_SEPARATOR . $filename;

        $etlTool = new EtlTool();
        $etlTool->setFlushCount(2);

        $loaderClosure = $this->container->get($definition->loader);
        $loader = $loaderClosure($definition, $dataFields, $exportPath, $filters);

        $extractorClosure = $this->container->get($definition->extractor);
        $extractor = $extractorClosure($definition, $dataFields, $exportPath, $filters);

        $etlTool->extractor($extractor)
                ->loader($loader);

        if ($definition->transformer !== null) {
            $etlTool->transformer($this->container->get($definition->transformer));
        }
        $etl = $etlTool->create();

        $etl->process($exportPath);

        return $exportPath;
    }

    /**
     * Return the definition fields
     * @param int $definitionId
     * @return array<string, mixed>
     */
    public function getDefinitionFields(int $definitionId): array
    {
        $definitionFields = $this->dataDefinitionFieldRepository->filters(['definition' => $definitionId])
                                                                ->orderBy('position')
                                                                ->all();

        $dataFields = [];
        $fieldNames = [];
        $columns = [];
        $displayNames = [];
        foreach ($definitionFields as $row) {
            $columns[] = $row->column;
            $fieldNames[] = $row->field;
            $displayNames[] = $row->name;

            $dataFields[] = [
              'field' => $row->field,
              'column' => $row->column,
              'display_name' => $row->name,
              'position' => $row->position,
              'default' => $row->default_value,
            ];
        }
        $dataFields['fields'] = $fieldNames;
        $dataFields['display_names'] = $displayNames;
        $dataFields['columns'] = $columns;

        return $dataFields;
    }
}
