<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Entity\DataDefinitionField;
use Platine\App\Module\Etl\Repository\DataDefinitionFieldRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\Database\Connection;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionCopyAction
* @package Platine\App\Module\Etl\Action
*/
class DataDefinitionCopyAction extends BaseAction
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
     * The Connection instance
     * @var Connection
     */
    protected Connection $connection;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
    * @param Connection $connection
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository,
        Connection $connection
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->connection = $connection;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $request = $this->request;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinition|null $dataDefinition */
        $dataDefinition = $this->dataDefinitionRepository->find($id);

        if ($dataDefinition === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_list');
        }

        $fields = $this->dataDefinitionFieldRepository->filters(['definition' => $id])
                                                                ->orderBy('position')
                                                                ->all();

        /** @var DataDefinition $dataDefinitionCopy */
        $dataDefinitionCopy = $this->dataDefinitionRepository->create([
            'model' => $dataDefinition->model,
            'extractor' => $dataDefinition->extractor,
            'transformer' => $dataDefinition->transformer,
            'filter' => $dataDefinition->filter,
            'name' => sprintf('Copie %s ', $dataDefinition->name),
            'description' => $dataDefinition->description,
            'loader' => $dataDefinition->loader,
            'direction' => $dataDefinition->direction,
            'status' => $dataDefinition->status,
            'header' => $dataDefinition->header,
            'field_separator' => $dataDefinition->field_separator,
            'text_delimiter' => $dataDefinition->text_delimiter,
            'escape_char' => $dataDefinition->escape_char,
            'extension' => $dataDefinition->extension,
            'enterprise_id' => $dataDefinition->enterprise_id,
            'user_id' => $this->authHelper->getUserId(),
        ]);

        $this->connection->startTransaction();
        try {
            $this->dataDefinitionRepository->save($dataDefinitionCopy);

            foreach ($fields as $field) {
                /** @var DataDefinitionField $fieldCopy */
                $fieldCopy = $this->dataDefinitionFieldRepository->create([
                    'field' => $field->field,
                    'name' => $field->name,
                    'column' => $field->column,
                    'transformer' => $field->transformer,
                    'parameters' => $field->parameters,
                    'position' => $field->position,
                    'default_value' => $field->default_value,
                    'enterprise_id' => $field->enterprise_id,
                    'user_id' => $this->authHelper->getUserId(),
                    'data_definition_id' => $dataDefinitionCopy->id,
                ]);

                $this->dataDefinitionFieldRepository->save($fieldCopy);
            }

            $this->connection->commit();

            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));

            return $this->redirect('data_definition_detail', ['id' => $dataDefinitionCopy->id]);
        } catch (Exception $ex) {
            $this->connection->rollback();
            $this->logger->error('Error when saved the data {error}', [
                'error' => $ex->getMessage()
            ]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->redirect('data_definition_detail', ['id' => $id]);
        }
    }
}
