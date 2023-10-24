<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action;

use Platine\App\Enum\YesNoStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Enum\DataDefinitionDirection;
use Platine\App\Module\Etl\Repository\DataDefinitionFieldRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionDetailAction
* @package Platine\App\Module\Etl\Action
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
        $this->setView('etl/definition/detail');

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
        $this->addContext('data_definition_field_transformer', $this->statusList->getDataDefinitionFieldTransformer());
        $this->addContext('data_definition_filter', $this->statusList->getDataDefinitionFilter());
        $this->addContext('status', $this->statusList->getYesNoStatus());

        $definitionFields = $this->dataDefinitionFieldRepository->filters(['definition' => $id])
                                                                ->orderBy('position')
                                                                ->all();

        $this->addContext('fields', $definitionFields);

        $this->addSidebar('', 'Définitions', 'data_definition_list');
        $this->addSidebar('', 'Nouvelle définition', 'data_definition_create');
        $this->addSidebar('', 'Copier', 'data_definition_copy', ['id' => $id], ['confirm' => true]);
        $this->addSidebar('', 'Modifier', 'data_definition_edit', ['id' => $id]);
        $this->addSidebar('', 'Ajouter un attribut', 'data_definition_field_create', ['id' => $id]);
        if (count($definitionFields) === 0) {
            $this->addSidebar('', 'Supprimer', 'data_definition_delete', ['id' => $id], ['confirm' => true]);
        }
        if ($dataDefinition->direction === DataDefinitionDirection::OUT && $dataDefinition->status === YesNoStatus::YES) {
            $this->addSidebar('', 'Exporter les données', 'data_definition_export_process', ['id' => $id]);
        }

        return $this->viewResponse();
    }
}
