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
 *  @file BaseAction.php
 *
 *  The base action class
 *
 *  @package    Platine\App\Http\Action
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\App\Http\Action;

use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\AuthHelper;
use Platine\App\Helper\FileHelper;
use Platine\App\Helper\Sidebar;
use Platine\App\Helper\StatusList;
use Platine\App\Helper\ViewContext;
use Platine\Config\Config;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Pagination\Pagination;
use Platine\Template\Template;


/**
 * @class BaseAction
 * @package Platine\App\Http\Action
 * @template T
 */
abstract class BaseAction implements RequestHandlerInterface
{
    /**
     * The field to use in query
     * @var string[]
     */
    protected array $fields = [];

    /**
     * The field columns maps
     * @var array<string, string>
     */
    protected array $fieldMaps = [];

    /**
     * The filter list
     * @var array<string, mixed>
     */
    protected array $filters = [];

     /**
     * The filters name maps
     * @var array<string, string>
     */
    protected array $filterMaps = [];

    /**
     * The sort information's
     * @var array<string, string>
     */
    protected array $sorts = [];

    /**
     * The pagination limit
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * The pagination current page
     * @var int|null
     */
    protected ?int $page = null;

    /**
     * Whether to query all list without pagination
     * @var bool
     */
    protected bool $all = false;

    /**
     * The name of the view
     * @var string
     */
    protected string $viewName = '';

    /**
     * The data export type
     * @var string
     */
    protected string $exportType = '';

    /**
     * The pagination instance
     * @var Pagination
     */
    protected Pagination $pagination;

    /**
     * The request to use
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The request data instance
     * @var RequestData
     */
    protected RequestData $param;

    /**
     * The Sidebar instance
     * @var Sidebar
     */
    protected Sidebar $sidebar;

    /**
     * The view context
     * @var ViewContext
     */
    protected ViewContext $context;

    /**
     * The template instance
     * @var Template
     */
    protected Template $template;

    /**
    * The RouteHelper instance
    * @var RouteHelper
    */
    protected RouteHelper $routeHelper;

    /**
    * The Flash instance
    * @var Flash
    */
    protected Flash $flash;

    /**
    * The Lang instance
    * @var Lang
    */
    protected Lang $lang;

    /**
    * The LoggerInterface instance
    * @var LoggerInterface
    */
    protected LoggerInterface $logger;

    /**
     * The status list instance
     * @var StatusList
     */
    protected StatusList $statusList;

    /**
     * The action helper instance
     * @var ActionHelper
     */
    protected ActionHelper $actionHelper;

    /**
     * The application configuration instance
     * @var Config
     */
    protected Config $config;

    /**
     * The application database configuration
     * @var AppDatabaseConfig
     */
    protected AppDatabaseConfig $dbConfig;

    /**
     * The file helper instance
     * @var FileHelper<T>
     */
    protected FileHelper $fileHelper;

    /**
     * The auth helper instance
     * @var AuthHelper
     */
    protected AuthHelper $authHelper;

    /**
     * Create new instance
     * @param ActionHelper $actionHelper
     */
    public function __construct(ActionHelper $actionHelper)
    {
        $this->actionHelper = $actionHelper;
        $this->pagination = $actionHelper->getPagination();
        $this->sidebar = $actionHelper->getSidebar();
        $this->context = $actionHelper->getContext();
        $this->template = $actionHelper->getTemplate();
        $this->routeHelper = $actionHelper->getRouteHelper();
        $this->flash = $actionHelper->getFlash();
        $this->lang = $actionHelper->getLang();
        $this->logger = $actionHelper->getLogger();
        $this->statusList = $actionHelper->getStatusList();
        $this->config = $actionHelper->getConfig();
        $this->dbConfig = $actionHelper->getDbConfig();
        $this->fileHelper = $actionHelper->getFileHelper();
        $this->authHelper = $actionHelper->getAuthHelper();
    }

    /**
     * {@inheritodc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->param = new RequestData($request);

        $this->setFields();
        $this->setFilters();
        $this->setSorts();
        $this->setPagination();

        return $this->respond();
    }

    /**
     * Set the view name
     * @param string $name
     * @return self
     */
    public function setView(string $name): self
    {
        $this->viewName = $name;

        return $this;
    }

    /**
     * Add sidebar
     * @inheritDoc
     * @see Sidebar
     * @param array<string, mixed> $params
     * @param array<string, mixed> $extras
     * @return self
     */
    public function addSidebar(
        string $group,
        string $title,
        string $name,
        array $params = [],
        array $extras = []
    ): self {
        $this->sidebar->add($group, $title, $name, $params, $extras);

        return $this;
    }

    /**
     * Add view context
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function addContext(string $name, $value): self
    {
        $this->context->set($name, $value);

        return $this;
    }

    /**
     * Add context in one call
     * @param array<string, mixed> $data
     * @return self
     */
    public function addContexts(array $data): self
    {
        foreach ($data as $name => $value) {
            $this->context->set($name, $value);
        }

        return $this;
    }

    /**
     * Return the template response
     * @return TemplateResponse
     */
    protected function viewResponse(): TemplateResponse
    {
        if (!empty($this->exportType)) {
            $this->addSidebar(
                'Exportation',
                'Exporter les données',
                'export_data',
                [],
                [
                    'query' => $this->filters + ['export_type' => $this->exportType]
                ]
            );
        }

        $sidebarContent = $this->sidebar->render();
        if (!empty($sidebarContent)) {
            $this->context['sidebar'] = $sidebarContent;
        }
        $this->context['pagination'] = $this->pagination->render();
        $this->context['app_url'] = $this->config->get('app.url');
        $this->context['request_method'] = $this->request->getMethod();

        // Application info
        $this->context['app_name'] = $this->config->get('app.name');
        $this->context['app_version'] = $this->config->get('app.version');

        // Used in the footer
        $this->context['current_year'] = date('Y');

        return new TemplateResponse(
            $this->template,
            $this->viewName,
            $this->context->all()
        );
    }

    /**
     * Redirect the user to another route
     * @param string $route
     * @param array<string, mixed> $params
     * @param array<string, mixed> $queries
     * @return RedirectResponse
     */
    protected function redirect(string $route, array $params = [], array $queries = []): RedirectResponse
    {
        $queriesStr = null;
        if (count($queries) > 0) {
            $queriesStr = http_build_query($queries);
        }
        $routeUrl = $this->routeHelper->generateUrl($route, $params);
        if ($queriesStr !== null) {
            $routeUrl .= '?' . $queriesStr;
        }

        return new RedirectResponse(
            $routeUrl
        );
    }

    /**
     * Return the REST response
     * @return ResponseInterface
     */
    abstract public function respond(): ResponseInterface;

    /**
     * Set field information's
     * @return void
     */
    protected function setFields(): void
    {
        $fieldParams = $this->param->get('fields', '');
        if (!empty($fieldParams)) {
            $fields = explode(',', $fieldParams);
            $columns = [];
            foreach ($fields as $field) {
                $columns[] = $this->fieldMaps[$field] ?? $field;
            }
            $this->fields = $columns;
        }
    }

    /**
     * Set filters information's
     * @return void
     */
    protected function setFilters(): void
    {
        $queries = $this->param->gets();
        //remove defaults
        unset(
            $queries['fields'],
            $queries['sort'],
            $queries['page'],
            $queries['limit'],
            $queries['all'],
            $queries['enterprise'], // very important for security reason because can access data from others
        );

        $filterParams = $queries;
        if (!empty($filterParams)) {
            $filters = [];
            foreach ($filterParams as $key => $value) {
                $name = $this->filterMaps[$key] ?? $key;
                if (is_string($value)) {
                    if (strlen((string) $value) > 0) {
                        $filters[$name] = $value;
                    }
                } elseif (is_array($value)) {
                    // handle empty value
                    if (count($value) > 1 || (count($value) === 1 && strlen((string) $value[0]) > 0)) {
                        $filters[$name] = $value;
                    }
                }
            }

            $this->filters = $filters;
        }

        //If user set default date
        $this->handleFilterDefault();

        //handle dates filter
        if (array_key_exists('start_date', $this->filters)) {
            $startDate = $this->filters['start_date'];
            // if no time provided xxxx-xx-xx
            if (strlen($startDate) === 10) {
                $startDate .= ' 00:00:00';
            }
            $this->filters['start_date'] = $startDate;
        }

        if (array_key_exists('end_date', $this->filters)) {
            $endDate = $this->filters['end_date'];
            // if no time provided xxxx-xx-xx
            if (strlen($endDate) === 10) {
                $endDate .= ' 23:59:59';
            }
            $this->filters['end_date'] = $endDate;
        }

        $ignoreDateFilters = $this->getIgnoreDateFilters();
        foreach ($ignoreDateFilters as $filterName) {
            if (array_key_exists($filterName, $this->filters)) {
                unset(
                    $this->filters['start_date'],
                    $this->filters['end_date']
                );
                break;
            }
        }
    }

    /**
     * Set sort information's
     * @return void
     */
    protected function setSorts(): void
    {
        $sortParams = $this->param->get('sort', '');
        if (!empty($sortParams)) {
            $sorts = explode(',', $sortParams);
            $columns = [];
            foreach ($sorts as $sort) {
                $order = 'ASC';
                $parts = explode(':', $sort);

                if (isset($parts[1]) && strtolower($parts[1]) === 'desc') {
                    $order = 'DESC';
                }
                $column = $this->fieldMaps[$parts[0]] ?? $parts[0];
                $columns[$column] = $order;
            }
            $this->sorts = $columns;
        }
    }

    /**
     * Set the pagination information
     * @return void
     */
    protected function setPagination(): void
    {
        $param = $this->param;

        if ($param->get('all', null)) {
            $this->all = true;
            return;
        }

        $limit = $param->get('limit', null);
        if ($limit) {
            $this->limit = (int) $limit;
        }

        $page = $param->get('page', null);
        if ($page) {
            $this->page = (int) $page;
        }

        if ($limit || $page) {
            $this->all = false;
        }

        if ($this->limit) {
            $this->pagination->setItemsPerPage($this->limit);
        }

        $currentPage = $this->page ?? 1;

        $this->pagination->setCurrentPage($currentPage);
    }

    /**
     * Parse the error message to handle delete or update of parent record
     * @param string $error
     * @return string
     */
    protected function parseForeignConstraintErrorMessage(string $error): string
    {
        $result = '';
        if (strpos($error, 'Cannot delete or update a parent row') !== false) {
            $arr = explode('.', $error);
            $tmp = explode(',', $arr[1]);
            $result = $this->lang->tr('Cette donnée dépend de %s', str_replace('_', ' ', $tmp[0]));
        }

        return $result;
    }


    /**
     * Handle filter default dates
     * @return void
     */
    protected function handleFilterDefault(): void
    {
    }

   /**
    * Ignore date filters if one of the given filters
    * is present
    * @return array<string> $filters
    */
    protected function getIgnoreDateFilters(): array
    {
        return [];
    }

    /**
     * Redirect back to origin if user want to create new entity from
     * detail page
     * @return ResponseInterface|null
     */
    protected function redirectBackToOrigin(): ?ResponseInterface
    {
        $param = $this->param;
        $originId = (int) $param->get('origin_id', 0);
        $originRoute = $param->get('origin_route');

        if ($originRoute === null) {
            return null;
        }

        if ($originId === 0) {
            return $this->redirect($originRoute);
        }

        return $this->redirect($originRoute, ['id' => $originId]);
    }
}
