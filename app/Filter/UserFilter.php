<?php

declare(strict_types=1);

namespace Platine\App\Filter;

use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\Filter;

/**
* @class UserFilter
* @package Platine\App\Filter
*/
class UserFilter extends Filter
{
    /**
     * The action helper
     * @var ActionHelper
     */
    protected ActionHelper $actionHelper;

    /**
     * Create new instance
     * @param ActionHelper $actionHelper
     */
    public function __construct(ActionHelper $actionHelper)
    {
        $this->actionHelper = $actionHelper;
        parent::__construct();
    }

    /**
    * {@inheritdoc}
    */
    public function configure(): self
    {
        $lang = $this->actionHelper->getLang();

        $this->addSelectField(
            'status',
            $lang->tr('Statut'),
            [
                'A' => 'Actif',
                'D' => 'Bloqué',
            ],
            '',
            [
                'class' => 'select2js'
            ]
        );

        return $this;
    }
}
