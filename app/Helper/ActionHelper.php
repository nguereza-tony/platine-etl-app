<?php

declare(strict_types=1);

namespace Platine\App\Helper;

use Platine\Config\Config;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Http\RouteHelper;
use Platine\Lang\Lang;
use Platine\Logger\LoggerInterface;
use Platine\Pagination\Pagination;
use Platine\Template\Template;

/**
 * @class ActionHelper
 * @package Platine\App\Helper
 * @template T
 */
class ActionHelper
{
    /**
     * The pagination instance
     * @var Pagination
     */
    protected Pagination $pagination;

    /**
     * The Sidebar instance
     * @var Sidebar
     */
    protected Sidebar $sidebar;

    /**
     * The view context
     * @var ViewContext<T>
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
     * The application configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The application database configuration
     * @var AppDatabaseConfig<T>
     */
    protected AppDatabaseConfig $dbConfig;

    /**
     * The file helper instance
     * @var FileHelper<T>
     */
    protected FileHelper $fileHelper;

    /**
     * Create new instance
     * @param Pagination $pagination
     * @param Sidebar $sidebar
     * @param ViewContext<T> $context
     * @param FileHelper<T> $fileHelper
     * @param Template $template
     * @param RouteHelper $routeHelper
     * @param Flash $flash
     * @param Lang $lang
     * @param LoggerInterface $logger
     * @param StatusList $statusList
     * @param Config<T> $config
     * @param AppDatabaseConfig<T> $dbConfig
     */
    public function __construct(
        Pagination $pagination,
        Sidebar $sidebar,
        ViewContext $context,
        FileHelper $fileHelper,
        Template $template,
        RouteHelper $routeHelper,
        Flash $flash,
        Lang $lang,
        LoggerInterface $logger,
        StatusList $statusList,
        Config $config,
        AppDatabaseConfig $dbConfig
    ) {
        $this->pagination = $pagination;
        $this->sidebar = $sidebar;
        $this->context = $context;
        $this->fileHelper = $fileHelper;
        $this->template = $template;
        $this->routeHelper = $routeHelper;
        $this->flash = $flash;
        $this->lang = $lang;
        $this->logger = $logger;
        $this->statusList = $statusList;
        $this->config = $config;
        $this->dbConfig = $dbConfig;
    }

    /**
     *
     * @return FileHelper
     */
    public function getFileHelper(): FileHelper
    {
        return $this->fileHelper;
    }


    /**
     * Return the database configuration instance
     * @return AppDatabaseConfig<T>
     */
    public function getDbConfig(): AppDatabaseConfig
    {
        return $this->dbConfig;
    }


    /**
     * Return the configuration instance
     * @return Config<T>
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Return the status list
     * @return StatusList
     */
    public function getStatusList(): StatusList
    {
        return $this->statusList;
    }

        /**
     *
     * @return Flash
     */
    public function getFlash(): Flash
    {
        return $this->flash;
    }

    /**
     *
     * @return Lang
     */
    public function getLang(): Lang
    {
        return $this->lang;
    }

    /**
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     *
     * @return Pagination
     */
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    /**
     *
     * @return Sidebar
     */
    public function getSidebar(): Sidebar
    {
        return $this->sidebar;
    }

    /**
     *
     * @return ViewContext<T>
     */
    public function getContext(): ViewContext
    {
        return $this->context;
    }

    /**
     *
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     *
     * @return RouteHelper
     */
    public function getRouteHelper(): RouteHelper
    {
        return $this->routeHelper;
    }
}
