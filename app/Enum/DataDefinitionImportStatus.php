<?php

declare(strict_types=1);

namespace Platine\App\Enum;

/**
* @class DataDefinitionImportStatus
* @package Platine\App\Enum
*/
class DataDefinitionImportStatus
{
    public const PENDING = 'P';
    public const ERROR = 'E';
    public const PROCESSED = 'C';
    public const CANCELLED = 'X';
}
