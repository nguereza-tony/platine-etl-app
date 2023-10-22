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
 *  @file AppServiceProvider.php
 *
 *  The Application service provider class
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

use Platine\App\Http\Action\DataDefinition\DataDefinitionCreateAction;
use Platine\App\Http\Action\DataDefinition\DataDefinitionDeleteAction;
use Platine\App\Http\Action\DataDefinition\DataDefinitionDetailAction;
use Platine\App\Http\Action\DataDefinition\DataDefinitionEditAction;
use Platine\App\Http\Action\DataDefinition\DataDefinitionListAction;
use Platine\App\Http\Action\DataDefinition\Export\DataDefinitionExportListAction;
use Platine\App\Http\Action\DataDefinition\Export\DataDefinitionExportProcessAction;
use Platine\App\Http\Action\DataDefinition\Field\DataDefinitionFieldCreateAction;
use Platine\App\Http\Action\DataDefinition\Field\DataDefinitionFieldDeleteAction;
use Platine\App\Http\Action\DataDefinition\Field\DataDefinitionFieldEditAction;
use Platine\App\Http\Action\DataDefinition\Import\DataDefinitionImportCreateAction;
use Platine\App\Http\Action\DataDefinition\Import\DataDefinitionImportDetailAction;
use Platine\App\Http\Action\DataDefinition\Import\DataDefinitionImportListAction;
use Platine\App\Http\Action\DataDefinition\Import\DataDefinitionImportProcessAction;
use Platine\Framework\Service\ServiceProvider;
use Platine\Route\Router;

/**
 * @class DataDefinitionServiceProvider
 * @package Platine\App\Provider
 */
class DataDefinitionServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(DataDefinitionListAction::class);
        $this->app->bind(DataDefinitionDetailAction::class);
        $this->app->bind(DataDefinitionCreateAction::class);
        $this->app->bind(DataDefinitionEditAction::class);
        $this->app->bind(DataDefinitionDeleteAction::class);
        $this->app->bind(DataDefinitionFieldCreateAction::class);
        $this->app->bind(DataDefinitionFieldDeleteAction::class);
        $this->app->bind(DataDefinitionFieldEditAction::class);
        $this->app->bind(DataDefinitionExportListAction::class);
        $this->app->bind(DataDefinitionExportProcessAction::class);
        $this->app->bind(DataDefinitionImportListAction::class);
        $this->app->bind(DataDefinitionImportCreateAction::class);
        $this->app->bind(DataDefinitionImportDetailAction::class);
        $this->app->bind(DataDefinitionImportProcessAction::class);
    }

    /**
     * {@inheritdoc}
     */
    public function addRoutes(Router $router): void
    {
        $router->group('/definitions', function (Router $router) {
            $router->get('', DataDefinitionListAction::class, 'data_definition_list');
            $router->get('/detail/{id}', DataDefinitionDetailAction::class, 'data_definition_detail');
            $router->get('/delete/{id}', DataDefinitionDeleteAction::class, 'data_definition_delete');
            $router->add('/create', DataDefinitionCreateAction::class, ['GET', 'POST'], 'data_definition_create');
            $router->add('/update/{id}', DataDefinitionEditAction::class, ['GET', 'POST'], 'data_definition_edit');

            // Export
            $router->group('/export', function (Router $router) {
                $router->get('', DataDefinitionExportListAction::class, 'data_definition_export_list');
                $router->add('/process/{id}', DataDefinitionExportProcessAction::class, ['GET', 'POST'], 'data_definition_export_process');
            });

            // Import
            $router->group('/import', function (Router $router) {
                $router->get('', DataDefinitionImportListAction::class, 'data_definition_import_list');
                $router->add('/create', DataDefinitionImportCreateAction::class, ['GET', 'POST'], 'data_definition_import_create');
                $router->get('/detail/{id}', DataDefinitionImportDetailAction::class, 'data_definition_import_detail');
                $router->get('/process/{id}', DataDefinitionImportProcessAction::class, 'data_definition_import_process');
            });

            $router->group('/fields', function (Router $router) {
                $router->add('/create/{id}', DataDefinitionFieldCreateAction::class, ['GET', 'POST'], 'data_definition_field_create');
                $router->add('/update/{id}', DataDefinitionFieldEditAction::class, ['GET', 'POST'], 'data_definition_field_edit');
                $router->get('/delete/{id}', DataDefinitionFieldDeleteAction::class, 'data_definition_field_delete');
            });
        });
    }
}
