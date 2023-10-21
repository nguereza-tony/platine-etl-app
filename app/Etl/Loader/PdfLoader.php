<?php

declare(strict_types=1);

namespace Platine\App\Etl\Loader;

use Generator;
use Platine\App\Model\Entity\DataDefinition;
use Platine\Etl\Etl;
use Platine\Etl\Loader\LoaderInterface;
use Platine\PDF\PDF;
use Platine\Stdlib\Helper\Json;
use Platine\Template\Template;
use RuntimeException;

class PdfLoader implements LoaderInterface
{
    /**
     *
     * @var PDF
     */
    protected PDF $pdf;

    /**
     * The data definition fields
     * @var array<string, mixed>
     */
    protected array $dataFields = [];

    /**
     * The filters
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * The data definition instance
     * @var DataDefinition
     */
    protected DataDefinition $dataDefinition;

    /**
     * Export path
     * @var string
     */
    protected string $path;

    /**
     * The JSON data
     * @var array<int|string, mixed>
     */
    protected array $data = [];

    /**
     * The Template instance
     * @var Template
     */
    protected Template $template;

    /**
     *
     * @param PDF $pdf
     */
    public function __construct(
        PDF $pdf,
        DataDefinition $dataDefinition,
        Template $template,
        string $path,
        array $dataFields = [],
        array $filters = []
    ) {
        $this->pdf = $pdf;
        $this->dataDefinition = $dataDefinition;
        $this->template = $template;
        $this->dataFields = $dataFields;
        $this->filters = $filters;
        $this->path = $path;
    }


    public function commit(bool $partial): void
    {
        if ($partial) {
            return;
        }

        $html = $this->template->render('definition/export/pdf', [
            'definition' => $this->dataDefinition,
            'data' => $this->data,
            'headers' => $this->dataFields['display_names'] ?? [],
            'fields' => $this->dataFields['fields'] ?? [],
        ]);

        $this->pdf->setContent($html)
                  ->setFilename($this->path)
                   ->generate()
                   ->save();
    }

    public function init(array $options = []): void
    {
        $this->data = [];
    }

    public function load(Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $k => $v) {
            $this->data[$k] = $v;
        }
    }

    public function rollback(): void
    {
        $this->queryBuilder->getConnection()->rollback();
    }
}
