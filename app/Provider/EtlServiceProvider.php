<?php

/**
 * Platine PHP
 *
 * Platine PHP is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine PHP
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
 *  @file EtlServiceProvider.php
 *
 *  The ETL service provider class
 *
 *  @package    Platine\App\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\App\Provider;

use Platine\App\Etl\Extractor\DbExtractor;
use Platine\App\Etl\Extractor\RepositoryExtractor;
use Platine\Config\Config;
use Platine\Container\ContainerInterface;
use Platine\Database\QueryBuilder;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\CsvExtractor;
use Platine\Etl\Loader\CsvFileLoader;
use Platine\Etl\Loader\JsonFileLoader;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Service\ServiceProvider;

/**
 * @class EtlServiceProvider
 * @package Platine\App\Provider
 */
class EtlServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->share('simple_transformer', function (ContainerInterface $app) {
            return function ($value, $key, Etl $etl, array $options = []) {
                if (empty($value['updated_at'])) {
                    $value['updated_at'] = null;
                }

                yield $key => $value;
            };
        });

        $this->app->share('entity_transformer', function (ContainerInterface $app) {
            return function ($value, $key, Etl $etl, array $options = []) {
                if (empty($value->updated_at)) {
                    $value->updated_at = null;
                }

                yield $key => $value->jsonSerialize();
            };
        });

        $this->app->share('json_file_loader', function (ContainerInterface $app) {
            $exportPath = $app->get(Config::class)->get('platform.data_export_path');
            $exportDir = $app->get(Filesystem::class)->directory($exportPath);
            $exportJsonPath = $exportDir->getPath() . DIRECTORY_SEPARATOR . 'export.json';

            return new JsonFileLoader($exportJsonPath, JSON_PRETTY_PRINT);
        });

        $this->app->share('csv_file_loader', function (ContainerInterface $app) {
            $exportPath = $app->get(Config::class)->get('platform.data_export_path');
            $exportDir = $app->get(Filesystem::class)->directory($exportPath);
            $exportCsvPath = $exportDir->getPath() . DIRECTORY_SEPARATOR . 'export.csv';

            return new CsvFileLoader($exportCsvPath);
        });

        $this->app->share('csv_file_extractor', function (ContainerInterface $app) {
            return new CsvExtractor(
                CsvExtractor::EXTRACT_FROM_FILE,
                true
            );
        });

        $this->app->share('db_extractor', function (ContainerInterface $app) {
            return new DbExtractor($app->get(QueryBuilder::class));
        });

        $this->app->share('repository_extractor', function (ContainerInterface $app) {
            return new RepositoryExtractor($app);
        });
    }
}
