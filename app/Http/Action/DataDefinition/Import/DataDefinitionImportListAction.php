<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition\Import;

use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\DataDefinitionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Repository\DataDefinitionImportRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionImportListAction
* @package Platine\App\Http\Action\DataDefinition\Import
*/
class DataDefinitionImportListAction extends BaseAction
{
    /**
    * The ActionHelper instance
    * @var ActionHelper
    */
    protected ActionHelper $actionHelper;

    /**
    * The DataDefinitionImportRepository instance
    * @var DataDefinitionImportRepository
    */
    protected DataDefinitionImportRepository $dataDefinitionImportRepository;


    /**
     * The DataDefinitionHelper instance
     * @var DataDefinitionHelper
     */
    protected DataDefinitionHelper $dataDefinitionHelper;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionImportRepository $dataDefinitionImportRepository
    * @param DataDefinitionHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionImportRepository $dataDefinitionImportRepository,
        DataDefinitionHelper $dataDefinitionHelper
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionImportRepository = $dataDefinitionImportRepository;
        $this->dataDefinitionHelper = $dataDefinitionHelper;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('definition/import/list');
        $param = $this->param;

        $totalItems = $this->dataDefinitionImportRepository->query()
                                                            ->filter($this->filters)
                                                            ->count('id');

        $this->addContext('total_items', $totalItems);

        $currentPage = (int) $param->get('page', 1);
        $this->pagination->setTotalItems($totalItems)
                         ->setCurrentPage($currentPage);

        $limit = $this->pagination->getItemsPerPage();
        $offset = $this->pagination->getOffset();

        $results = $this->dataDefinitionImportRepository->limit($offset, $limit)
                                                    ->with(['file', 'definition'])
                                                    ->filters($this->filters)
                                                    ->orderBy(['id'], 'DESC')
                                                    ->all();

        $this->addContext('list', $results);
        $this->addContext('status', $this->statusList->getDataDefinitionImportStatus());

        $this->addSidebar('', 'Importer un fichier', 'data_definition_import_create');
        $this->addSidebar('', 'Définitions', 'data_definition_list');

        return $this->viewResponse();
    }
}
