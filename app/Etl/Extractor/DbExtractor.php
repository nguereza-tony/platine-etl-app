<?php

declare(strict_types=1);

namespace Platine\App\Etl\Extractor;

use Platine\Database\QueryBuilder;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\ExtractorInterface;
use Platine\Orm\Entity;

class DbExtractor implements ExtractorInterface
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

    public function extract($input, Etl $etl): iterable
    {
        $results = $this->queryBuilder->from($input)
                                     ->select()
                                     ->fetchAssoc()
                                     ->all();

        return $results;
    }
}
