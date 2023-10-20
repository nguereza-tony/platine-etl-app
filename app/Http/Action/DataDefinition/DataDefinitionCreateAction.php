<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Entity\DataDefinition;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\App\Param\DataDefinitionParam;
use Platine\App\Validator\DataDefinitionValidator;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionCreateAction
* @package Platine\App\Http\Action\DataDefinition
*/
class DataDefinitionCreateAction extends BaseAction
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
        $this->setView('definition/create');
        $param = $this->param;
        $request = $this->request;

        $formParam = new DataDefinitionParam($param->posts());
        $this->addContext('param', $formParam);

        $this->addContext('direction', $this->statusList->getDataDefinitionDirection());
        $this->addContext('data_definition_repository', $this->statusList->getDataDefinitionRepository());
        $this->addContext('data_definition_loader', $this->statusList->getDataDefinitionLoader());
        $this->addContext('data_definition_extractor', $this->statusList->getDataDefinitionExtractor());
        $this->addContext('data_definition_transformer', $this->statusList->getDataDefinitionTransformer());
        $this->addContext('status', $this->statusList->getYesNoStatus());

        if ($request->getMethod() === 'GET') {
            //If user come from detail
            $fromId = $param->get('from', null);
            if ($fromId !== null) {
                $from = $this->dataDefinitionRepository->find($fromId);
                if ($from) {
                    $this->addContext('param', (new DataDefinitionParam())->fromEntity($from));
                }
            }
            return $this->viewResponse();
        }

        $validator = new DataDefinitionValidator($formParam, $this->lang, $this->statusList);
        if ($validator->validate() === false) {
            $this->addContext('errors', $validator->getErrors());

            return $this->viewResponse();
        }

        $direction = $formParam->getDirection();
        if (
            $this->dataDefinitionRepository->findBy([
                'name' => $formParam->getName(),
            ]) !== null
        ) {
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

        /** @var DataDefinition $dataDefinition */
        $dataDefinition = $this->dataDefinitionRepository->create([
            'name' => $formParam->getName(),
            'extractor' => $formParam->getExtractor(),
            'loader' => $formParam->getLoader(),
            'description' => $description,
            'transformer' => $transformer,
            'direction' => $direction,
            'field_separator' => $fieldSeparator,
            'text_delimiter' => $textDelimiter,
            'escape_char' => $escapeChar,
            'status' => $formParam->getStatus(),
            'header' => $formParam->getHeader(),
        ]);

        try {
            $this->dataDefinitionRepository->save($dataDefinition);

            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));

            return $this->redirect('data_definition_detail', ['id' => $dataDefinition->id]);
        } catch (Exception $ex) {
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->viewResponse();
        }
    }
}
