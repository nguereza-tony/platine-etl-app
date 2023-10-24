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
 *  @package    Platine\App\Module\Etl\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\App\Module\Etl\Provider;

use Platine\App\Enum\YesNoStatus;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Extractor\RepositoryExtractor;
use Platine\App\Module\Etl\Helper\EtlHelper;
use Platine\App\Module\Etl\Loader\PdfLoader;
use Platine\App\Module\Etl\Loader\RepositoryLoader;
use Platine\Container\ContainerInterface;
use Platine\Database\Connection;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\CsvExtractor;
use Platine\Etl\Extractor\JsonExtractor;
use Platine\Etl\Loader\CsvFileLoader;
use Platine\Etl\Loader\JsonFileLoader;
use Platine\Framework\Service\ServiceProvider;
use Platine\PDF\PDF;
use Platine\Template\Template;


/**
 * @class EtlServiceProvider
 * @package Platine\App\Module\Etl\Provider
 */
class EtlServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(EtlHelper::class);

        $this->registerExtractors();
        $this->registerTransformers();
        $this->registerLoaders();
    }


    /**
     * Register the extractors
     * @return void
     */
    protected function registerExtractors(): void
    {
        $this->app->share('csv_file_extractor', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) {
                return new CsvExtractor(
                    CsvExtractor::EXTRACT_FROM_FILE,
                    $definition->header === YesNoStatus::YES,
                    $definition->field_separator ?? ',',
                    $definition->text_delimiter ?? '"',
                    $definition->escape_char ?? '\\'
                );
            };
        });

        $this->app->share('json_file_extractor', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) {
                return new JsonExtractor(JsonExtractor::EXTRACT_FROM_FILE);
            };
        });


        $this->app->share('repository_extractor', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) use ($app) {
                return new RepositoryExtractor($app, $definition, $dataFields, $filters);
            };
        });
    }

    /**
     * Register the transformers
     * @return void
     */
    protected function registerTransformers(): void
    {
        $this->app->share('entity_import_transformer', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) {
                return function ($value, $key, Etl $etl, array $options = []) {
                    yield $key => $value;
                };
            };
        });

        $this->app->share('entity_transformer', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) {
                return function ($value, $key, Etl $etl, array $options = []) {
                    $data = $value->jsonSerialize();
                    if (array_key_exists('updated_at', $data) && $value->updated_at !== null) {
                        $data['updated_at'] = $value->updated_at->format('Y-m-d H:i:s');
                    }

                    if (array_key_exists('created_at', $data) && $value->created_at !== null) {
                        $data['created_at'] = $value->created_at->format('Y-m-d H:i:s');
                    }

                    yield $key => $data;
                };
            };
        });
    }

    /**
     * Register the loaders
     * @return void
     */
    protected function registerLoaders(): void
    {
        $this->app->share('json_file_loader', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) {
                return new JsonFileLoader($path, JSON_PRETTY_PRINT);
            };
        });

        $this->app->share('csv_file_loader', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) {
                return new CsvFileLoader(
                    $path,
                    $dataFields['display_names'] ?? [],
                    $definition->field_separator ?? ',',
                    $definition->text_delimiter ?? '"',
                    $definition->escape_char ?? '\\'
                );
            };
        });

        $this->app->share('entity_import_loader', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) use ($app) {
                return new RepositoryLoader(
                    $definition,
                    $app,
                    $app->get(Connection::class),
                    $dataFields
                );
            };
        });

        $this->app->share('pdf_file_loader', function (ContainerInterface $app) {
            return function (
                DataDefinition $definition,
                array $dataFields,
                string $path,
                array $filters = []
            ) use ($app) {
                return new PdfLoader(
                    $app->get(PDF::class),
                    $definition,
                    $app->get(Template::class),
                    $path,
                    $dataFields,
                    $filters
                );
            };
        });
    }
}
