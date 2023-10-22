<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition\Import;

use Platine\App\Enum\DataDefinitionImportStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\DataDefinitionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Entity\DataDefinitionImport;
use Platine\App\Model\Repository\DataDefinitionImportRepository;
use Platine\Database\Connection;
use Platine\Http\ResponseInterface;
use Platine\Stdlib\Helper\Json;
use Throwable;

/**
* @class DataDefinitionImportProcessAction
* @package Platine\App\Http\Action\DataDefinition\Import
*/
class DataDefinitionImportProcessAction extends BaseAction
{
    /**
    * The ActionHelper instance
    * @var ActionHelper
    */
    protected ActionHelper $actionHelper;

    /**
    * The DataDefinitionImportRepository instance
    * @var DataDefinitionImportRepository
    */
    protected DataDefinitionImportRepository $dataDefinitionImportRepository;


    /**
     * The DataDefinitionHelper instance
     * @var DataDefinitionHelper
     */
    protected DataDefinitionHelper $dataDefinitionHelper;

    /**
     * The Connection instance
     * @var Connection
     */
    protected Connection $connection;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionImportRepository $dataDefinitionImportRepository
    * @param Connection $connection
    * @param DataDefinitionHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionImportRepository $dataDefinitionImportRepository,
        Connection $connection,
        DataDefinitionHelper $dataDefinitionHelper
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionImportRepository = $dataDefinitionImportRepository;
        $this->dataDefinitionHelper = $dataDefinitionHelper;
        $this->connection = $connection;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $request = $this->request;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinitionImport|null $dataDefinitionImport */
        $dataDefinitionImport = $this->dataDefinitionImportRepository->filters([
                                                                        'status' => DataDefinitionImportStatus::PENDING
                                                                    ])
                                                                     ->find($id);

        if ($dataDefinitionImport === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_import_list');
        }

        // Can take too much time depend to data to load
        set_time_limit(0);

        $this->connection->startTransaction();
        try {
            $result = $this->dataDefinitionHelper->import($dataDefinitionImport);

            if ($result['success']) {
                $status = DataDefinitionImportStatus::PROCESSED;
                $this->flash->setSuccess($this->lang->tr('Données importées avec succès'));
            } else {
                $this->flash->setError($this->lang->tr('Erreur lors de l\'importation des données'));
                $status = DataDefinitionImportStatus::ERROR;
            }

            $this->dataDefinitionImportRepository->query()
                                                 ->where('id')->is($id)
                                                 ->update([
                                                     'status' => $status,
                                                     'total' => $result['total'],
                                                     'processed' => $result['processed'],
                                                     'error' => $result['error'],
                                                     'error_items' => Json::encode($result['error_items'], JSON_PRETTY_PRINT),
                                                     'processed_items' => Json::encode($result['processed_items'], JSON_PRETTY_PRINT),
                                                  ]);

            $this->connection->commit();

            return $this->redirect('data_definition_import_detail', ['id' => $id]);
        } catch (Throwable $ex) {
             $this->connection->rollback();
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->redirect('data_definition_import_detail', ['id' => $id]);
        }
    }
}
