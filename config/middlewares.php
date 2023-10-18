<?php

use Platine\Framework\Http\Middleware\ErrorHandlerMiddleware;
use Platine\Framework\Http\Middleware\RouteDispatcherMiddleware;
use Platine\Framework\Http\Middleware\RouteMatchMiddleware;

    return [
        ErrorHandlerMiddleware::class,
        RouteMatchMiddleware::class,
       // SecurityPolicyMiddleware::class,
        RouteDispatcherMiddleware::class,
    ];
