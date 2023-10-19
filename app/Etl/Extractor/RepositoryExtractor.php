<?php

declare(strict_types=1);

namespace Platine\App\Etl\Extractor;

use Platine\Container\ContainerInterface;
use Platine\Etl\Etl;
use Platine\Etl\Extractor\ExtractorInterface;
use Platine\Orm\RepositoryInterface;

class RepositoryExtractor implements ExtractorInterface
{
    /**
     *
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function extract($input, Etl $etl, array $options = []): iterable
    {
        $fields = $options['fields'] ?? [];
        $definition = $options['definition'];

        /** @var RepositoryInterface $repository */
        $repository = $this->container->get($definition->model);

        $results = $repository->query()
                              ->all($fields['fields'] ?? [], false);


        return $results;
    }
}
