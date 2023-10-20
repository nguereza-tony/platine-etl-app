<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file HomeAction.php
 *
 *  The Platine Welcome action class
 *
 *  @package    Platine\App\Http\Action
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\App\Http\Action;

use Platine\App\Helper\ActionHelper;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\Container\ContainerInterface;
use Platine\Database\QueryBuilder;
use Platine\Etl\EtlTool;
use Platine\Etl\Event\FlushEvent;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Http\ResponseInterface;
use RuntimeException;

/**
 * @class HomeAction
 * @package Platine\App\Http\Action
 */
class HomeAction extends BaseAction
{
    /**
     * The application instance
     * @var Application
     */
    protected Application $app;

    /**
     *
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     *
     * @var DataDefinitionRepository
     */
    protected DataDefinitionRepository $dataDefinitionRepository;

    /**
     *
     * @var DataDefinitionFieldRepository
     */
    protected DataDefinitionFieldRepository $dataDefinitionFieldRepository;

    protected QueryBuilder $queryBuilder;


    /**
     *
     * @var ActionHelper
     */
    protected ActionHelper $actionHelper;

    /**
     *
     * @var ContainerInterface
     */
    protected ContainerInterface $container;


    public function __construct(
        ActionHelper $actionHelper,
        ContainerInterface $container,
        Filesystem $filesystem,
        QueryBuilder $queryBuilder,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository
    ) {
        parent::__construct($actionHelper);
        $this->container = $container;
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->filesystem = $filesystem;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function respond(): ResponseInterface
    {
        $param = $this->param;
        $this->setView('home');
        $definitionId = (int) $param->get('definition', 1);
        $definition = $this->dataDefinitionRepository->find($definitionId);
        if ($definition === null) {
            throw new RuntimeException(sprintf('Can not found data definition with id [%d]', $definitionId));
        }

        $dataFields = $this->getDefinitionFields($definitionId);


        $context = [];
        $importCsvFilename = 'import.csv';
        $importJsonFilename = 'import.json';
        $tmpPath = $this->config->get('platform.data_temp_path');
        $exportPath = $this->config->get('platform.data_export_path');
        $tmpDir = $this->filesystem->directory($tmpPath);
        $exportDir = $this->filesystem->directory($exportPath);

        $importCsvPath = $tmpDir->getPath() . DIRECTORY_SEPARATOR . $importCsvFilename;
        $importJsonPath = $tmpDir->getPath() . DIRECTORY_SEPARATOR . $importJsonFilename;

        $etlTool = new EtlTool();
        $etlTool->setFlushCount(2);

        $etlTool->extractor($this->container->get($definition->extractor))
                ->loader($this->container->get($definition->loader))
                ->onFlush(function (FlushEvent $e) {
                    $this->logger->info('Flush data -> Is partial: {partial}, counter: {counter}', [
                        'counter' => $e->getCounter(),
                        'partial' => $e->isPartial() ? 'Yes' : 'No',
                    ]);
                });

        if ($definition->transformer !== null) {
            $etlTool->transformer($this->container->get($definition->transformer));
        }
        $etl = $etlTool->create();

        $etl->process($importCsvPath, [
            'definition' => $definition,
            'fields' => $dataFields,
            'loader' => [
                'keys' => $dataFields['display_names']
            ]
        ]);

        $this->sidebar->add('', 'Home', 'home');

        return $this->viewResponse();
    }


    protected function getDefinitionFields(int $definitionId): array
    {
        $definitionFields = $this->dataDefinitionFieldRepository->filters(['definition' => $definitionId])
                                                                ->orderBy('position')
                                                                ->all();

        $dataFields = [];
        $fieldNames = [];
        $displayNames = [];
        foreach ($definitionFields as $row) {
            $position = $row->position;
            $default = $row->default_value;
            $field = $row->field;
            $displayName = $row->name;

            $fieldNames[] = $field;
            $displayNames[] = $displayName;

            $dataFields[] = [
              'field' => $field,
              'display_name' => $displayName,
              'position' => $position,
              'default' => $default,
            ];
        }
        $dataFields['fields'] = $fieldNames;
        $dataFields['display_names'] = $displayNames;

        return $dataFields;
    }
}
