<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action\Import;

use Platine\App\Module\Etl\Enum\DataDefinitionImportStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Module\Etl\Helper\EtlHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinitionImport;
use Platine\App\Module\Etl\Repository\DataDefinitionImportRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionImportDetailAction
* @package Platine\App\Module\Etl\Action\Import
*/
class DataDefinitionImportDetailAction extends BaseAction
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
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionImportRepository $dataDefinitionImportRepository
    * @param EtlHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionImportRepository $dataDefinitionImportRepository,
        EtlHelper $dataDefinitionHelper
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionImportRepository = $dataDefinitionImportRepository;
        $this->dataDefinitionHelper = $dataDefinitionHelper;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('etl/import/detail');

        $request = $this->request;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinitionImport|null $dataDefinitionImport */
        $dataDefinitionImport = $this->dataDefinitionImportRepository->find($id);

        if ($dataDefinitionImport === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_import_list');
        }

        if ($dataDefinitionImport->error_items !== null) {
            $dataDefinitionImport->error_items = unserialize($dataDefinitionImport->error_items);
        }

        if ($dataDefinitionImport->processed_items !== null) {
            $dataDefinitionImport->processed_items = unserialize($dataDefinitionImport->processed_items);
        }

        $this->addContext('import', $dataDefinitionImport);
        $this->addContext('data_definition_repository', $this->statusList->getDataDefinitionRepository());
        $this->addContext('data_definition_loader', $this->statusList->getDataDefinitionLoader());
        $this->addContext('data_definition_extractor', $this->statusList->getDataDefinitionExtractor());
        $this->addContext('data_definition_transformer', $this->statusList->getDataDefinitionTransformer());
        $this->addContext('data_definition_filter', $this->statusList->getDataDefinitionFilter());
        $this->addContext('status', $this->statusList->getYesNoStatus());
        $this->addContext('definition_import_status', $this->statusList->getDataDefinitionImportStatus());


        $this->addSidebar('', 'Importations', 'data_definition_import_list');
        $this->addSidebar('', 'Importer un fichier', 'data_definition_import_create');
        $this->addSidebar('', 'Définitions', 'data_definition_list');
        if ($dataDefinitionImport->status === DataDefinitionImportStatus::PENDING) {
            $this->addSidebar('', 'Exécuter', 'data_definition_import_process', ['id' => $id], ['confirm' => true]);
            $this->addSidebar('', 'Annuler', 'data_definition_import_cancel', ['id' => $id], ['confirm' => true]);
        }
        $this->addSidebar('', 'Supprimer', 'data_definition_import_delete', ['id' => $id], ['confirm' => true]);

        return $this->viewResponse();
    }
}
