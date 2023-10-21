<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition\Field;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Entity\DataDefinition;
use Platine\App\Model\Entity\DataDefinitionField;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\App\Param\DataDefinitionFieldParam;
use Platine\App\Validator\DataDefinitionFieldValidator;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionFieldCreateAction
* @package Platine\App\Http\Action\DataDefinition\Field
*/
class DataDefinitionFieldCreateAction extends BaseAction
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
        $this->setView('definition/field/create');

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

        $definitionFields = $this->dataDefinitionFieldRepository->filters(['definition' => $id])
                                                                ->with(['parent'])
                                                                ->orderBy('position')
                                                                ->all();

        $this->addContext('fields', $definitionFields);

        $formParam = new DataDefinitionFieldParam($param->posts());
        $this->addContext('param', $formParam);

        if ($request->getMethod() === 'GET') {
            $maxPosition = (int) $this->dataDefinitionFieldRepository->query()
                                                               ->filter(['definition' => $id])
                                                               ->max('position');
            $this->context['param']->setPosition((string) ++$maxPosition);

            return $this->viewResponse();
        }

        $validator = new DataDefinitionFieldValidator($formParam, $this->lang);
        if ($validator->validate() === false) {
            $this->addContext('errors', $validator->getErrors());

            return $this->viewResponse();
        }

        if (
            $this->dataDefinitionFieldRepository->findBy([
                'field' => $formParam->getField(),
                'data_definition_id' => $id,
            ]) !== null
        ) {
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

        /** @var DataDefinitionField $dataDefinitionField */
        $dataDefinitionField = $this->dataDefinitionFieldRepository->create([
            'name' => $formParam->getName(),
            'field' => $formParam->getField(),
            'position' => (int) $formParam->getPosition(),
            'default_value' => $defaultValue,
            'parent_id' => $parent,
            'column' => $column,
            'data_definition_id' => $id,
        ]);

        try {
            $this->dataDefinitionFieldRepository->save($dataDefinitionField);

            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));

            return $this->redirect('data_definition_detail', ['id' => $id]);
        } catch (Exception $ex) {
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->viewResponse();
        }
    }
}
