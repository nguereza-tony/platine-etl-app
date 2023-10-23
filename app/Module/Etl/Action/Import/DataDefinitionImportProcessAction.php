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
use Platine\Stdlib\Helper\Arr;
use Throwable;

/**
* @class DataDefinitionImportProcessAction
* @package Platine\App\Module\Etl\Action\Import
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
                                                     'error_items' => serialize($result['error_items']),
                                                     'processed_items' => serialize($result['processed_items']),
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
