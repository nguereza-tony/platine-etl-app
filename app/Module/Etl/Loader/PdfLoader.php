<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Loader;

use Generator;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\Etl\Etl;
use Platine\Etl\Loader\LoaderInterface;
use Platine\PDF\PDF;
use Platine\Template\Template;

/**
 * @class PdfLoader
 * @package Platine\App\Module\Etl\Loader
 */
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
     * @param DataDefinition $dataDefinition
     * @param Template $template
     * @param string $path
     * @param array<string, mixed> $dataFields
     * @param array<string, mixed> $filters
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


    /**
    * {@inheritdoc}
    */
    public function commit(bool $partial): void
    {
        if ($partial) {
            return;
        }

        $html = $this->template->render('etl/export/pdf', [
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

    /**
    * {@inheritdoc}
    */
    public function init(array $options = []): void
    {
        $this->data = [];
    }

    /**
    * {@inheritdoc}
    * @param Generator<int|string, mixed> $items
    */
    public function load(Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $k => $v) {
            $this->data[$k] = $v;
        }
    }

    /**
    * {@inheritdoc}
    */
    public function rollback(): void
    {
    }
}
