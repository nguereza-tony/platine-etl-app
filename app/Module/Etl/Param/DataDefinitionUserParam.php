<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Param;

use Platine\App\Param\AppParam;

/**
* @class DataDefinitionUserParam
* @package Platine\App\Module\Etl\Param
*/
class DataDefinitionUserParam extends AppParam
{
    /**
    * The users field
    * @var array<int>
    */
    protected array $users = [];

    /**
     *
     * @return int[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     *
     * @param int[] $users
     * @return $this
     */
    public function setUsers(array $users)
    {
        $this->users = $users;
        return $this;
    }
}
