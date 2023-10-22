<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action\Field;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinitionField;
use Platine\App\Module\Etl\Repository\DataDefinitionFieldRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionFieldDeleteAction
* @package Platine\App\Module\Etl\Action\Field
*/
class DataDefinitionFieldDeleteAction extends BaseAction
{
    /**
    * The ActionHelper instance
    * @var ActionHelper
    */
    protected ActionHelper $actionHelper;

    /**
    * The DataDefinitionFieldRepository instance
    * @var DataDefinitionFieldRepository
    */
    protected DataDefinitionFieldRepository $dataDefinitionFieldRepository;



    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $request = $this->request;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinitionField|null $dataDefinitionField */
        $dataDefinitionField = $this->dataDefinitionFieldRepository->find($id);

        if ($dataDefinitionField === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_list');
        }

        $dataDefinitionId = $dataDefinitionField->data_definition_id;

        try {
            $this->dataDefinitionFieldRepository->delete($dataDefinitionField);

            $this->flash->setSuccess($this->lang->tr('Donnée supprimée avec succès'));

            return $this->redirect('data_definition_detail', ['id' => $dataDefinitionId]);
        } catch (Exception $ex) {
            $error = $this->parseForeignConstraintErrorMessage($ex->getMessage());

            $this->logger->error('Error when delete the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données (%s)', $error));

            return $this->redirect('data_definition_detail', ['id' => $dataDefinitionId]);
        }
    }
}
