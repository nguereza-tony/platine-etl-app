<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action;

use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionListAction
* @package Platine\App\Module\Etl\Action
*/
class DataDefinitionListAction extends BaseAction
{
    /**
    * The ActionHelper instance
    * @var ActionHelper
    */
    protected ActionHelper $actionHelper;

    /**
    * The DataDefinitionRepository instance
    * @var DataDefinitionRepository
    */
    protected DataDefinitionRepository $dataDefinitionRepository;



    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('etl/definition/list');
        $param = $this->param;

        $totalItems = $this->dataDefinitionRepository->query()
                                            ->filter($this->filters)
                                            ->count('id');

        $this->addContext('total_items', $totalItems);

        $currentPage = (int) $param->get('page', 1);
        $this->pagination->setTotalItems($totalItems)
                         ->setCurrentPage($currentPage);

        $limit = $this->pagination->getItemsPerPage();
        $offset = $this->pagination->getOffset();

        $results = $this->dataDefinitionRepository->limit($offset, $limit)
                                       ->filters($this->filters)
                                       ->orderBy(['name'])
                                       ->all();

        $this->addContext('list', $results);
        $this->addContext('direction', $this->statusList->getDataDefinitionDirection());
         $this->addContext('status', $this->statusList->getYesNoStatus());


        $this->addSidebar('', 'Nouvelle définition', 'data_definition_create');
        $this->addSidebar('', 'Exporter les données', 'data_definition_export_list');

        return $this->viewResponse();
    }
}
