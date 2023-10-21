<?php

declare(strict_types=1);

namespace Platine\App\Etl\Loader;

use Generator;
use Platine\Database\QueryBuilder;
use Platine\Etl\Etl;
use Platine\Etl\Loader\LoaderInterface;

class DbLoader implements LoaderInterface
{
    /**
     *
     * @var QueryBuilder
     */
    protected QueryBuilder $queryBuilder;

    /**
     *
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }


    public function commit(bool $partial): void
    {
        $this->queryBuilder->getConnection()->commit();
        if ($partial) {
            $this->queryBuilder->getConnection()->startTransaction();
        }
    }

    public function init(array $options = []): void
    {
        $this->queryBuilder->getConnection()->startTransaction();
    }

    public function load(Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $item) {
            if ($item[0]) {
                // insert
                $this->queryBuilder->insert($item[1])
                                   ->into($item[2]);
            } else {
                $this->queryBuilder->update($item[2])
                                    ->where($item[3])
                                    ->set($item[1]);
            }
        }
    }

    public function rollback(): void
    {
        $this->queryBuilder->getConnection()->rollback();
    }
}
