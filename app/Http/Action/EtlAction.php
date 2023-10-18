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
 *  @file EtlAction.php
 *
 *  The ETL action class
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

use Platine\App\Etl\Extractor\DbExtractor;
use Platine\App\Etl\Loader\DbLoader;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\App\Model\Repository\DataMappingRepository;
use Platine\Config\Config;
use Platine\Database\Query\WhereStatement;
use Platine\Database\QueryBuilder;
use Platine\Etl\Etl;
use Platine\Etl\EtlTool;
use Platine\Etl\Event\FlushEvent;
use Platine\Etl\Extractor\CsvExtractor;
use Platine\Etl\Loader\CsvFileLoader;
use Platine\Etl\Loader\JsonFileLoader;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Template\Template;

/**
 * @class EtlAction
 * @package Platine\App\Http\Action
 */
class EtlAction implements RequestHandlerInterface
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
        $context = [];
        $importCsvFilename = 'import.csv';
        $importJsonFilename = 'import.json';
        $tmpPath = $this->config->get('platform.data_temp_path');
        $exportPath = $this->config->get('platform.data_export_path');
        $tmpDir = $this->filesystem->directory($tmpPath);
        $exportDir = $this->filesystem->directory($exportPath);

        $importCsvPath = $tmpDir->getPath() . DIRECTORY_SEPARATOR . $importCsvFilename;
        $importJsonPath = $tmpDir->getPath() . DIRECTORY_SEPARATOR . $importJsonFilename;

        $exportJsonPath = $exportDir->getPath() . DIRECTORY_SEPARATOR . 'export.json';
        $exportCsvPath = $exportDir->getPath() . DIRECTORY_SEPARATOR . 'export.csv';

        $context['tmp_path'] = $tmpDir->getPath();
        $context['export_path'] = $exportDir->getPath();
        $context['import_csv_path'] = $importCsvPath;
        $context['import_json_path'] = $importJsonPath;

        $etlTool = new EtlTool();
        $etlTool->setFlushCount(2);

        $dbLoader = new DbLoader($this->queryBuilder);
        $dbExtractor = new DbExtractor($this->queryBuilder);

        $etl = $etlTool->extractor(new CsvExtractor(
            CsvExtractor::EXTRACT_FROM_FILE,
            ',',
            '"',
            '\\',
            true
        ))
        ->extractor($dbExtractor)
        ->loader(new JsonFileLoader($exportJsonPath, JSON_PRETTY_PRINT))
        ->loader(new CsvFileLoader(
            $exportCsvPath,
            ['id', 'User name', 'Email', 'Password', 'Status', 'Last Name', 'First Name', 'Created At', 'Updated At']
        ))
       // ->loader($dbLoader)
        ->transformer($this->simpleDbExportTransformer())
        // ->transformer($this->simpleTransformer())
        // ->transformer($this->dbTransformer($this->queryBuilder))
        ->onFlush(function (FlushEvent $e) {
            $this->logger->info('Flush data -> Is partial: {partial}, counter: {counter}', [
                'counter' => $e->getCounter(),
                'partial' => $e->isPartial() ? 'Yes' : 'No',
            ]);
        })
        ->create();

        // $etl->process($importCsvPath);
        $etl->process('users');

        return new TemplateResponse(
            $this->template,
            'home',
            $context
        );
    }

    protected function simpleTransformer(): callable
    {
        return function ($value, $key, Etl $etl) {
            $value['can_email'] = $value['can_email'] === 'Y' ? 'Yes' : 'No';
            $value['can_call'] = $value['can_call'] === 'Y' ? 'Yes' : 'No';
            if (empty($value['updated_at'])) {
                $value['updated_at'] = null;
            }

            if ($value['lastname'] === 'POUTINE') {
               // $etl->skipCurrentItem();
            }

            yield $key => $value;
        };
    }

    protected function simpleDbExportTransformer(): callable
    {
        return function ($value, $key, Etl $etl) {
            if (empty($value['updated_at'])) {
                $value['updated_at'] = null;
            }

            if ($value['lastname'] === 'POUTINE') {
               // $etl->skipCurrentItem();
            }

            yield $key => $value;
        };
    }

    /**
     *
     * @param QueryBuilder $qb
     * @return callable
     */
    protected function dbTransformer(QueryBuilder $qb): callable
    {
        return function ($value, $key, Etl $etl) use ($qb) {
            $count = (int) $qb->from('users')
                        ->where('username')->is($value['email'])
                        ->orWhere('email')->is($value['email'])
                        ->count('id');

            if ($count === 0) {
                yield [
                    true,
                    [
                        'lastname' => $value['lastname'],
                        'firstname' => $value['firstname'],
                        'username' => $value['email'],
                        'email' => $value['email'],
                        'password' => uniqid(),
                        'status' => 'A',
                        'created_at' => date('Y-m-d H:i:s'),
                    ],
                    'users'
                ];
            } else {
                yield [
                    false,
                    [
                        'lastname' => $value['lastname'],
                        'firstname' => $value['firstname'],
                        'username' => $value['email'],
                        'email' => $value['email'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    'users',
                    function (WhereStatement $where) use ($value) {
                        $where->where('username')->is($value['email'])
                              ->orWhere('email')->is($value['email']);
                    }
                ];
            }
        };
    }
}
