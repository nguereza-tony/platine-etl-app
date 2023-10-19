<?php

declare(strict_types=1);

namespace Platine\App\Etl\Extractor;

use Platine\Database\QueryBuilder;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\ExtractorInterface;

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

    public function extract($input, Etl $etl, array $options = []): iterable
    {
        $fields = $options['fields'] ?? [];
        $definition = $options['definition'];

        $results = $this->queryBuilder->from($definition->model)
                                     ->select($fields['fields'] ?? [])
                                     ->fetchAssoc()
                                     ->all();

        return $results;
    }
}
