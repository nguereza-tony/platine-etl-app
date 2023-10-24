<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action\Import;

use Exception;
use Platine\App\Enum\YesNoStatus;
use Platine\App\Exception\AppUploadException;
use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Enum\DataDefinitionDirection;
use Platine\App\Module\Etl\Enum\DataDefinitionImportStatus;
use Platine\App\Module\Etl\Param\DataDefinitionImportParam;
use Platine\App\Module\Etl\Repository\DataDefinitionImportRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionUserRepository;
use Platine\App\Module\Etl\Validator\DataDefinitionImportValidator;
use Platine\Database\Connection;
use Platine\Http\ResponseInterface;
use Platine\Stdlib\Helper\Arr;

/**
* @class DataDefinitionImportCreateAction
* @package Platine\App\Module\Etl\Action\Import
*/
class DataDefinitionImportCreateAction extends BaseAction
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
    * The DataDefinitionImportRepository instance
    * @var DataDefinitionImportRepository
    */
    protected DataDefinitionImportRepository $dataDefinitionImportRepository;

    /**
     * The Connection instance
     * @var Connection
     */
    protected Connection $connection;

    /**
    * The DataDefinitionUserRepository instance
    * @var DataDefinitionUserRepository
    */
    protected DataDefinitionUserRepository $dataDefinitionUserRepository;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    * @param DataDefinitionImportRepository $dataDefinitionImportRepository
    * @param DataDefinitionUserRepository $dataDefinitionUserRepository
    * @param Connection $connection
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionImportRepository $dataDefinitionImportRepository,
        DataDefinitionUserRepository $dataDefinitionUserRepository,
        Connection $connection
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionImportRepository = $dataDefinitionImportRepository;
        $this->dataDefinitionUserRepository = $dataDefinitionUserRepository;
        $this->connection = $connection;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('etl/import/create');
        $param = $this->param;
        $request = $this->request;

        $formParam = new DataDefinitionImportParam($param->posts());
        $this->addContext('param', $formParam);

        $this->addContext('status', $this->statusList->getDataDefinitionImportStatus());

        $definitionList = $this->dataDefinitionUserRepository->query()
                                                       ->filter(['user' => $this->authHelper->getUserId()])
                                                        ->all(['data_definition_id'], false);

        $definitionsId = Arr::getColumn($definitionList, ['data_definition_id']);
        $definitions = [];
        if (count($definitionsId) > 0) {
            $definitions = $this->dataDefinitionRepository->filters([
                                                        'status' => YesNoStatus::YES,
                                                        'direction' => DataDefinitionDirection::IN,
                                                    ])
                                                    ->orderBy(['name'])
                                                    ->findAll(...$definitionsId);
        }

        if (count($definitions) === 0) {
            $this->flash->setError($this->lang->tr('Aucune définition des données disponibles pour importation'));

            return $this->redirect('data_definition_import_list');
        }

        $this->addContext('definitions', $definitions);

        if ($request->getMethod() === 'GET') {
            return $this->viewResponse();
        }

        $formParam->setStatus(DataDefinitionImportStatus::PENDING);
        $validator = new DataDefinitionImportValidator($formParam, $this->lang);
        if ($validator->validate() === false) {
            $this->addContext('errors', $validator->getErrors());

            return $this->viewResponse();
        }

        if ($this->fileHelper->isUploaded('file') === false) {
            $this->flash->setError($this->lang->tr('Veuillez choisir un fichier'));

            return $this->viewResponse();
        }

        $description = $formParam->getDescription();

        try {
            $file = $this->fileHelper->uploadAttachment('file', $description, 'import');
        } catch (AppUploadException $ex) {
            $this->logger->error('Error when saved the uploaded file {error}', ['error' => $ex->getMessage()]);
            $this->flash->setError($this->lang->tr('Erreur lors de traitement du fichier'));
            $this->addContext('errors', ['file' => $ex->getMessage()]);

            return $this->viewResponse();
        }

        $this->connection->startTransaction();
        try {
            $import = $this->dataDefinitionImportRepository->create([
                'description' => $description,
                'total' => 0,
                'processed' => 0,
                'error' => 0,
                'status' => $formParam->getStatus(),
                'data_definition_id' => (int) $formParam->getDataDefinition(),
                'file_id' => $file->id,
                'enterprise_id' => $this->authHelper->getEnterpiseId(),
                'user_id' => $this->authHelper->getUserId(),
            ]);


            $this->dataDefinitionImportRepository->save($import);

            $this->connection->commit();

            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));


            return $this->redirect('data_definition_import_list');
        } catch (Exception $ex) {
            $this->connection->rollback();

            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->viewResponse();
        }
    }
}
