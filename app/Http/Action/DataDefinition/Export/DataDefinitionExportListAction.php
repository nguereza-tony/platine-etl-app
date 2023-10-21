<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition\Export;

use Platine\App\Enum\DataDefinitionDirection;
use Platine\App\Enum\YesNoStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\DataDefinitionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionExportListAction
* @package Platine\App\Http\Action\DataDefinition\Export
*/
class DataDefinitionExportListAction extends BaseAction
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
    * The DataDefinitionFieldRepository instance
    * @var DataDefinitionFieldRepository
    */
    protected DataDefinitionFieldRepository $dataDefinitionFieldRepository;

    /**
     * The DataDefinitionHelper instance
     * @var DataDefinitionHelper
     */
    protected DataDefinitionHelper $dataDefinitionHelper;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
    * @param DataDefinitionHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository,
        DataDefinitionHelper $dataDefinitionHelper
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->dataDefinitionHelper = $dataDefinitionHelper;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('definition/export/list');
        $param = $this->param;

        $totalItems = $this->dataDefinitionRepository->query()
                                            ->filter([
                                                'status' => YesNoStatus::YES,
                                                'direction' => DataDefinitionDirection::OUT
                                            ])
                                            ->count('id');

        $this->addContext('total_items', $totalItems);

        $currentPage = (int) $param->get('page', 1);
        $this->pagination->setTotalItems($totalItems)
                         ->setCurrentPage($currentPage);

        $limit = $this->pagination->getItemsPerPage();
        $offset = $this->pagination->getOffset();

        $results = $this->dataDefinitionRepository->limit($offset, $limit)
                                                    ->filters([
                                                        'status' => YesNoStatus::YES,
                                                        'direction' => DataDefinitionDirection::OUT
                                                    ])
                                                    ->orderBy(['name'])
                                                    ->all();

        $this->addContext('list', $results);
        $this->addContext('status', $this->statusList->getYesNoStatus());
        $this->addContext('data_definition_repository', $this->statusList->getDataDefinitionRepository());
        $this->addContext('data_definition_loader', $this->statusList->getDataDefinitionLoader());
        $this->addContext('data_definition_extractor', $this->statusList->getDataDefinitionExtractor());
        $this->addContext('data_definition_transformer', $this->statusList->getDataDefinitionTransformer());

        $this->addSidebar('', 'Définitions', 'data_definition_list');

        return $this->viewResponse();
    }
}
