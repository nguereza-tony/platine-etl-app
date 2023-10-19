<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition;

use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Entity\DataDefinition;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionDetailAction
* @package Platine\App\Http\Action\DataDefinition
*/
class DataDefinitionDetailAction extends BaseAction
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
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('definition/detail');

        $request = $this->request;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinition|null $dataDefinition */
        $dataDefinition = $this->dataDefinitionRepository->find($id);

        if ($dataDefinition === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_list');
        }

        $this->addContext('definition', $dataDefinition);
        $this->addContext('direction', $this->statusList->getDataDefinitionDirection());
        $this->addContext('data_definition_repository', $this->statusList->getDataDefinitionRepository());
        $this->addContext('data_definition_loader', $this->statusList->getDataDefinitionLoader());
        $this->addContext('data_definition_extractor', $this->statusList->getDataDefinitionExtractor());
        $this->addContext('data_definition_transformer', $this->statusList->getDataDefinitionTransformer());

        $definitionFields = $this->dataDefinitionFieldRepository->filters(['definition' => $id])
                                                                ->with(['mapping'])
                                                                ->orderBy('position')
                                                                ->all();

        $this->addContext('fields', $definitionFields);

        $this->addSidebar('', 'Définitions', 'data_definition_list');
        $this->addSidebar('', 'Nouvelle définition', 'home');
        $this->addSidebar('', 'Copier', 'home', [], ['query' => ['from' => $id]]);
        $this->addSidebar('', 'Modifier', 'data_definition_detail', ['id' => $id]);
        $this->addSidebar('', 'Supprimer', 'data_definition_detail', ['id' => $id], ['confirm' => true]);

        return $this->viewResponse();
    }
}
