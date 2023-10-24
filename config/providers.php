<?php

use Platine\App\Module\Etl\Provider\EtlActionServiceProvider;
use Platine\App\Module\Etl\Provider\EtlRepositoryServiceProvider;
use Platine\App\Module\Etl\Provider\EtlRouteServiceProvider;
use Platine\App\Module\Etl\Provider\EtlServiceProvider;
use Platine\App\Provider\AppServiceProvider;
use Platine\App\Provider\ConsoleServiceProvider;
use Platine\Framework\Service\Provider\AuditServiceProvider;
use Platine\Framework\Service\Provider\AuthServiceProvider;
use Platine\Framework\Service\Provider\CommandServiceProvider;
use Platine\Framework\Service\Provider\DatabaseConfigServiceProvider;
use Platine\Framework\Service\Provider\DatabaseServiceProvider;
use Platine\Framework\Service\Provider\ErrorHandlerServiceProvider;
use Platine\Framework\Service\Provider\FilesystemServiceProvider;
use Platine\Framework\Service\Provider\LangServiceProvider;
use Platine\Framework\Service\Provider\LoggerServiceProvider;
use Platine\Framework\Service\Provider\MigrationServiceProvider;
use Platine\Framework\Service\Provider\PaginationServiceProvider;
use Platine\Framework\Service\Provider\PDFServiceProvider;
use Platine\Framework\Service\Provider\RoutingServiceProvider;
use Platine\Framework\Service\Provider\SecurityServiceProvider;
use Platine\Framework\Service\Provider\SessionServiceProvider;
use Platine\Framework\Service\Provider\TemplateServiceProvider;

    return [
        //Framework
        LoggerServiceProvider::class,
        ErrorHandlerServiceProvider::class,
        RoutingServiceProvider::class,
        FilesystemServiceProvider::class,
        DatabaseServiceProvider::class,
        SessionServiceProvider::class,
        MigrationServiceProvider::class,
        // CacheServiceProvider::class,
        TemplateServiceProvider::class,
        // CookieServiceProvider::class,
        LangServiceProvider::class,
        CommandServiceProvider::class,
        AuthServiceProvider::class,
        // ApiAuthServiceProvider::class,
        AuditServiceProvider::class,
        PaginationServiceProvider::class,
        SecurityServiceProvider::class,
        ConsoleServiceProvider::class,
        DatabaseConfigServiceProvider::class,
        // SchedulerServiceProvider::class,

        //Custom
        AppServiceProvider::class,
        EtlServiceProvider::class,
        EtlActionServiceProvider::class,
        EtlRepositoryServiceProvider::class,
        EtlRouteServiceProvider::class,
        PDFServiceProvider::class,
    ];
