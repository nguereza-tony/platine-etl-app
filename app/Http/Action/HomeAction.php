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
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Http\ResponseInterface;

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


        $this->sidebar->add('', 'Home', 'home');

        return $this->viewResponse();
    }
}
