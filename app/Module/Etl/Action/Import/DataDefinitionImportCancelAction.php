<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action\Import;

use Platine\App\Helper\ActionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinitionImport;
use Platine\App\Module\Etl\Enum\DataDefinitionImportStatus;
use Platine\App\Module\Etl\Helper\EtlHelper;
use Platine\App\Module\Etl\Repository\DataDefinitionImportRepository;
use Platine\Database\Connection;
use Platine\Http\ResponseInterface;
use Throwable;

/**
* @class DataDefinitionImportCancelAction
* @package Platine\App\Module\Etl\Action\Import
*/
class DataDefinitionImportCancelAction extends BaseAction
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
     * The EtlHelper instance
     * @var EtlHelper
     */
    protected EtlHelper $dataDefinitionHelper;

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
    * @param EtlHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionImportRepository $dataDefinitionImportRepository,
        Connection $connection,
        EtlHelper $dataDefinitionHelper
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

        $this->connection->startTransaction();
        try {
            $dataDefinitionImport->status = DataDefinitionImportStatus::CANCELLED;
            $this->dataDefinitionImportRepository->save($dataDefinitionImport);

            $this->flash->setSuccess($this->lang->tr('Donnée enregistrée avec succès'));

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
