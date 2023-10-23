<?php

declare(strict_types=1);

namespace Platine\App\Module\Auth\Audit;

use Platine\Framework\Audit\AuditUserInterface;

/**
 * @class SessionUser
 * @package Platine\App\Module\Auth\Audi
 */
class SessionUser implements AuditUserInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserId(): int
    {
        return 1;
    }
}
