<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action\Export;

use Platine\App\Enum\YesNoStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Enum\DataDefinitionDirection;
use Platine\App\Module\Etl\Helper\EtlHelper;
use Platine\App\Module\Etl\Repository\DataDefinitionFieldRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionUserRepository;
use Platine\Http\ResponseInterface;
use Platine\Stdlib\Helper\Arr;

/**
* @class DataDefinitionExportListAction
* @package Platine\App\Module\Etl\Action\Export
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
     * The EtlHelper instance
     * @var EtlHelper
     */
    protected EtlHelper $dataDefinitionHelper;

    /**
    * The DataDefinitionUserRepository instance
    * @var DataDefinitionUserRepository
    */
    protected DataDefinitionUserRepository $dataDefinitionUserRepository;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
    * @param DataDefinitionUserRepository $dataDefinitionUserRepository
    * @param EtlHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository,
        DataDefinitionUserRepository $dataDefinitionUserRepository,
        EtlHelper $dataDefinitionHelper
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->dataDefinitionHelper = $dataDefinitionHelper;
        $this->dataDefinitionUserRepository = $dataDefinitionUserRepository;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('etl/export/list');
        $param = $this->param;

        $definitionList = $this->dataDefinitionUserRepository->query()
                                                       ->filter(['user' => $this->authHelper->getUserId()])
                                                        ->all(['data_definition_id'], false);

        $definitionsId = Arr::getColumn($definitionList, ['data_definition_id']);

        $totalItems = 0;
        if (count($definitionsId) > 0) {
            $totalItems = $this->dataDefinitionRepository->query()
                                                ->filter([
                                                    'status' => YesNoStatus::YES,
                                                    'direction' => DataDefinitionDirection::OUT,
                                                    'definitions' => $definitionsId
                                                ])
                                                ->count('id');
        }

        $this->addContext('total_items', $totalItems);

        $currentPage = (int) $param->get('page', 1);
        $this->pagination->setTotalItems($totalItems)
                         ->setCurrentPage($currentPage);

        $limit = $this->pagination->getItemsPerPage();
        $offset = $this->pagination->getOffset();

        $results = [];
        if (count($definitionsId) > 0) {
            $results = $this->dataDefinitionRepository->limit($offset, $limit)
                                                    ->filters([
                                                        'status' => YesNoStatus::YES,
                                                        'direction' => DataDefinitionDirection::OUT,
                                                        'definitions' => $definitionsId
                                                    ])
                                                    ->orderBy(['name'])
                                                    ->all();
        }

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
