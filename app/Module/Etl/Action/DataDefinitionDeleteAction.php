<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action;

use Exception;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Repository\DataDefinitionFieldRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionDeleteAction
* @package Platine\App\Module\Etl\Action
*/
class DataDefinitionDeleteAction extends BaseAction
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
        $request = $this->request;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinition|null $dataDefinition */
        $dataDefinition = $this->dataDefinitionRepository->find($id);

        if ($dataDefinition === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_list');
        }

        try {
            $this->dataDefinitionRepository->delete($dataDefinition);

            $this->flash->setSuccess($this->lang->tr('Donnée supprimée avec succès'));

            return $this->redirect('data_definition_list');
        } catch (Exception $ex) {
            $error = $this->parseForeignConstraintErrorMessage($ex->getMessage());

            $this->logger->error('Error when delete the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données (%s)', $error));

            return $this->redirect('data_definition_detail', ['id' => $id]);
        }
    }
}
