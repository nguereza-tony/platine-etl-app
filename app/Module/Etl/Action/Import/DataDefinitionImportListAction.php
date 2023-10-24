<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action\Import;

use Platine\App\Helper\ActionHelper;
use Platine\App\Module\Etl\Helper\EtlHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Repository\DataDefinitionImportRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionImportListAction
* @package Platine\App\Module\Etl\Action\Import
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
     * The EtlHelper instance
     * @var EtlHelper
     */
    protected EtlHelper $dataDefinitionHelper;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionImportRepository $dataDefinitionImportRepository
    * @param EtlHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionImportRepository $dataDefinitionImportRepository,
        EtlHelper $dataDefinitionHelper
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
        $this->setView('etl/import/list');
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
                                                    ->orderBy(['created_at'], 'DESC')
                                                    ->all();

        $this->addContext('list', $results);
        $this->addContext('status', $this->statusList->getDataDefinitionImportStatus());

        $this->addSidebar('', 'Importer un fichier', 'data_definition_import_create');
        $this->addSidebar('', 'Définitions', 'data_definition_list');

        return $this->viewResponse();
    }

    /**
    * {@inheritdoc}
    */
    protected function handleFilterDefault(): void
    {
        $this->filters['user'] = $this->authHelper->getUserId();
    }
}
