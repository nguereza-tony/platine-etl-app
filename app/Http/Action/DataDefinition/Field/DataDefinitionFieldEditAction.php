<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition\Field;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Entity\DataDefinitionField;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\App\Param\DataDefinitionFieldParam;
use Platine\App\Validator\DataDefinitionFieldValidator;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionFieldEditAction
* @package Platine\App\Http\Action\DataDefinition\Field
*/
class DataDefinitionFieldEditAction extends BaseAction
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
        $this->setView('definition/field/edit');

        $request = $this->request;
        $param = $this->param;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinitionField|null $dataDefinitionField */
        $dataDefinitionField = $this->dataDefinitionFieldRepository->find($id);

        if ($dataDefinitionField === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_list');
        }


        $this->addContext('definition', $dataDefinitionField->definition);

        $definitionFields = $this->dataDefinitionFieldRepository->filters([
                                                                   'definition' => $dataDefinitionField->data_definition_id
                                                                ])
                                                                ->with(['parent'])
                                                                ->orderBy('position')
                                                                ->all();

        $this->addContext('fields', $definitionFields);

        $this->addContext('param', (new DataDefinitionFieldParam())->fromEntity($dataDefinitionField));

        if ($request->getMethod() === 'GET') {
            return $this->viewResponse();
        }

        $formParam = new DataDefinitionFieldParam($param->posts());
        $this->addContext('param', $formParam);

        $validator = new DataDefinitionFieldValidator($formParam, $this->lang);
        if ($validator->validate() === false) {
            $this->addContext('errors', $validator->getErrors());

            return $this->viewResponse();
        }

        $exists = $this->dataDefinitionFieldRepository->findBy([
            'field' => $formParam->getField(),
            'data_definition_id' => $dataDefinitionField->data_definition_id,
        ]);

        if ($exists !== null && $exists->id !== $id) {
            $this->flash->setError($this->lang->tr('Cet enregistrement existe déjà'));

            return $this->viewResponse();
        }

        $defaultValue = $formParam->getDefaultValue();
        if (empty($defaultValue)) {
            $defaultValue = null;
        }

        $column = $formParam->getColumn();
        if (empty($column)) {
            $column = $formParam->getField();
        }

        $parent = (int) $formParam->getParent();
        if ($parent <= 0) {
            $parent = null;
        }

        $dataDefinitionField->name = $formParam->getName();
        $dataDefinitionField->field = $formParam->getField();
        $dataDefinitionField->position = (int) $formParam->getPosition();
        $dataDefinitionField->default_value = $defaultValue;
        $dataDefinitionField->parent_id = $parent;
        $dataDefinitionField->column = $column;

        try {
            $this->dataDefinitionFieldRepository->save($dataDefinitionField);

            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));

            return $this->redirect('data_definition_detail', ['id' => $dataDefinitionField->data_definition_id]);
        } catch (Exception $ex) {
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->viewResponse();
        }
    }
}
