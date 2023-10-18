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

use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\App\Model\Repository\DataMappingRepository;
use Platine\Config\Config;
use Platine\Database\QueryBuilder;
use Platine\Etl\EtlTool;
use Platine\Etl\Event\FlushEvent;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Template\Template;
use RuntimeException;

/**
 * @class HomeAction
 * @package Platine\App\Http\Action
 */
class HomeAction implements RequestHandlerInterface
{
    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
     * The application instance
     * @var Application
     */
    protected Application $app;

    /**
     *
     * @var Config
     */
    protected Config $config;

    /**
     *
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     *
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     *
     * @var QueryBuilder
     */
    protected QueryBuilder $queryBuilder;

    /**
     *
     * @var DataMappingRepository
     */
    protected DataMappingRepository $dataMappingRepository;

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


    public function __construct(
        Template $template,
        Application $app,
        Filesystem $filesystem,
        LoggerInterface $logger,
        QueryBuilder $queryBuilder,
        DataMappingRepository $dataMappingRepository,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository,
        Config $config
    ) {
        $this->dataMappingRepository = $dataMappingRepository;
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->template = $template;
        $this->app = $app;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $definitionId = 1;
        $definition = $this->dataDefinitionRepository->find($definitionId);
        if ($definition === null) {
            throw new RuntimeException(sprintf('Can not found data definition with id [%d]', $definitionId));
        }

        $context = [];
        $importCsvFilename = 'import.csv';
        $importJsonFilename = 'import.json';
        $tmpPath = $this->config->get('platform.data_temp_path');
        $exportPath = $this->config->get('platform.data_export_path');
        $tmpDir = $this->filesystem->directory($tmpPath);
        $exportDir = $this->filesystem->directory($exportPath);

        $importCsvPath = $tmpDir->getPath() . DIRECTORY_SEPARATOR . $importCsvFilename;
        $importJsonPath = $tmpDir->getPath() . DIRECTORY_SEPARATOR . $importJsonFilename;

        $context['tmp_path'] = $tmpDir->getPath();
        $context['export_path'] = $exportDir->getPath();
        $context['import_csv_path'] = $importCsvPath;
        $context['import_json_path'] = $importJsonPath;

        $etlTool = new EtlTool();
        $etlTool->setFlushCount(2);

        $etlTool->extractor($this->app->get($definition->extractor))
                ->loader($this->app->get($definition->loader))
                ->onFlush(function (FlushEvent $e) {
                    $this->logger->info('Flush data -> Is partial: {partial}, counter: {counter}', [
                        'counter' => $e->getCounter(),
                        'partial' => $e->isPartial() ? 'Yes' : 'No',
                    ]);
                });

        if ($definition->transformer !== null) {
            $etlTool->transformer($this->app->get($definition->transformer));
        }
        $etl = $etlTool->create();

        $etl->process($importCsvPath, ['definition' => $definition]);

        return new TemplateResponse(
            $this->template,
            'home',
            $context
        );
    }
}
