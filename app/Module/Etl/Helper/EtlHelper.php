<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Helper;

use Platine\App\Helper\FileHelper;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Entity\DataDefinitionImport;
use Platine\App\Module\Etl\Repository\DataDefinitionFieldRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionImportRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\Config\Config;
use Platine\Container\ContainerInterface;
use Platine\Etl\EtlTool;
use Platine\Etl\Event\ItemEvent;
use Platine\Etl\Event\ItemExceptionEvent;
use Platine\Logger\LoggerInterface;
use Platine\Stdlib\Helper\Str;

/**
 * @class EtlHelper
 * @package Platine\App\Module\Etl\Helper
 */
class EtlHelper
{
    /**
     * The Container instance
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

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
     * The DataDefinitionImportRepository instance
     * @var DataDefinitionImportRepository
     */
    protected DataDefinitionImportRepository $dataDefinitionImportRepository;

    /**
     * The FileHelper instance
     * @var FileHelper
     */
    protected FileHelper $fileHelper;

    /**
     * The Config instance
     * @var Config
     */
    protected Config $config;

    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Create new instance
     * @param ContainerInterface $container
     * @param DataDefinitionRepository $dataDefinitionRepository
     * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
     * @param FileHelper $fileHelper
     * @param Config $config
     * @param LoggerInterface $logger
     * @param DataDefinitionImportRepository $dataDefinitionImportRepository
     */
    public function __construct(
        ContainerInterface $container,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository,
        FileHelper $fileHelper,
        Config $config,
        LoggerInterface $logger,
        DataDefinitionImportRepository $dataDefinitionImportRepository
    ) {
        $this->container = $container;
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->dataDefinitionImportRepository = $dataDefinitionImportRepository;
        $this->fileHelper = $fileHelper;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Process import of data
     * @param DataDefinitionImport $import
     * @return array<string, mixed>
     */
    public function import(DataDefinitionImport $import): array
    {
        $file = $import->file;
        $definition = $import->definition;

        $importPath = $this->fileHelper->getEnterprisePath(
            $this->config->get('platform.data_attachment_path'),
            true
        );

        $path = sprintf(
            '%simport%s%s',
            $importPath,
            DIRECTORY_SEPARATOR,
            $file->name
        );

        $dataFields = $this->getDefinitionFields($definition->id);

        $errorItems = [];
        $processItems = [];

        $etlTool = new EtlTool();
        $etlTool->setFlushCount(50);
        $etlTool->onLoadException(function (ItemExceptionEvent $e) use (&$errorItems) {
            $e->ignoreException();
            $errorItems[] = $e->getItem();
        })
        ->onLoad(function (ItemEvent $e) use (&$processItems) {
            $processItems[] = $e->getItem();
        });

        $loaderClosure = $this->container->get($definition->loader);
        $loader = $loaderClosure($definition, $dataFields, $path, []);

        $extractorClosure = $this->container->get($definition->extractor);
        $extractor = $extractorClosure($definition, $dataFields, $path, []);

        $etlTool->extractor($extractor)
                ->loader($loader);

        if ($definition->transformer !== null) {
            $transformerClosure = $this->container->get($definition->transformer);
            $transformer = $transformerClosure($definition, $dataFields, $path, []);
            $etlTool->transformer($transformer);
        }
        $etl = $etlTool->create();

        $etl->process($path);

        $processedCount = count($processItems);
        $errorCount = count($errorItems);
        $total = $processedCount + $errorCount;

        return [
            'processed_items' => $processItems,
            'error_items' => $errorItems,
            'processed' => $processedCount,
            'error' => $errorCount,
            'total' => $total,
            'success' => $errorCount === 0,
        ];
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
            $transformerClosure = $this->container->get($definition->transformer);
            $transformer = $transformerClosure($definition, $dataFields, $exportPath, $filters);
            $etlTool->transformer($transformer);
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

            $dataFields['data'][$row->field] = [
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
