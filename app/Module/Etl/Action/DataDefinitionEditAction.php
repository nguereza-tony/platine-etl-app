<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\App\Module\Etl\Param\DataDefinitionParam;
use Platine\App\Module\Etl\Validator\DataDefinitionValidator;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionEditAction
* @package Platine\App\Module\Etl\Action
*/
class DataDefinitionEditAction extends BaseAction
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
        $this->setView('etl/definition/edit');

        $request = $this->request;
        $param = $this->param;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinition|null $dataDefinition */
        $dataDefinition = $this->dataDefinitionRepository->find($id);

        if ($dataDefinition === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_list');
        }

        $this->addContext('definition', $dataDefinition);
        $this->addContext('param', (new DataDefinitionParam())->fromEntity($dataDefinition));

        $this->addContext('direction', $this->statusList->getDataDefinitionDirection());
        $this->addContext('data_definition_repository', $this->statusList->getDataDefinitionRepository());
        $this->addContext('data_definition_loader', $this->statusList->getDataDefinitionLoader());
        $this->addContext('data_definition_extractor', $this->statusList->getDataDefinitionExtractor());
        $this->addContext('data_definition_transformer', $this->statusList->getDataDefinitionTransformer());
        $this->addContext('data_definition_filter', $this->statusList->getDataDefinitionFilter());
        $this->addContext('status', $this->statusList->getYesNoStatus());

        if ($request->getMethod() === 'GET') {
            return $this->viewResponse();
        }

        $formParam = new DataDefinitionParam($param->posts());
        $this->addContext('param', $formParam);

        $validator = new DataDefinitionValidator($formParam, $this->lang, $this->statusList);
        if ($validator->validate() === false) {
            $this->addContext('errors', $validator->getErrors());

            return $this->viewResponse();
        }

        $direction = $formParam->getDirection();
        $exists = $this->dataDefinitionRepository->findBy([
            'name' => $formParam->getName(),
        ]);

        if ($exists !== null && $exists->id !== $id) {
            $this->flash->setError($this->lang->tr('Cet enregistrement existe déjà'));

            return $this->viewResponse();
        }

        $model = $formParam->getModel();
        if (empty($model)) {
            $model = null;
        }
        $description = $formParam->getDescription();
        if (empty($description)) {
            $description = null;
        }

        $transformer = $formParam->getTransformer();
        if (empty($transformer)) {
            $transformer = null;
        }

        $filter = $formParam->getFilter();
        if (empty($filter)) {
            $filter = null;
        }

        $fieldSeparator = $formParam->getFieldSeparator();
        if (empty($fieldSeparator)) {
            $fieldSeparator = null;
        }

        $textDelimiter = $formParam->getTextDelimiter();
        if (empty($textDelimiter)) {
            $textDelimiter = null;
        }

        $escapeChar = $formParam->getEscapeChar();
        if (empty($escapeChar)) {
            $escapeChar = null;
        }

        $dataDefinition->name = $formParam->getName();
        $dataDefinition->extractor = $formParam->getExtractor();
        $dataDefinition->loader = $formParam->getLoader();
        $dataDefinition->description = $description;
        $dataDefinition->transformer = $transformer;
        $dataDefinition->model = $model;
        $dataDefinition->filter = $filter;
        $dataDefinition->direction = $direction;
        $dataDefinition->field_separator = $fieldSeparator;
        $dataDefinition->text_delimiter = $textDelimiter;
        $dataDefinition->escape_char = $escapeChar;
        $dataDefinition->status = $formParam->getStatus();
        $dataDefinition->header = $formParam->getHeader();
        $dataDefinition->extension = $formParam->getExtension();

        try {
            $this->dataDefinitionRepository->save($dataDefinition);

            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));

            return $this->redirect('data_definition_detail', ['id' => $id]);
        } catch (Exception $ex) {
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->viewResponse();
        }
    }
}
