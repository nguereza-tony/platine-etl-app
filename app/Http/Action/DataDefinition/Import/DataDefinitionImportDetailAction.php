<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition\Import;

use Platine\App\Enum\DataDefinitionImportStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\DataDefinitionHelper;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Entity\DataDefinitionImport;
use Platine\App\Model\Repository\DataDefinitionImportRepository;
use Platine\Http\ResponseInterface;

/**
* @class DataDefinitionImportDetailAction
* @package Platine\App\Http\Action\DataDefinition\Import
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
     * The DataDefinitionHelper instance
     * @var DataDefinitionHelper
     */
    protected DataDefinitionHelper $dataDefinitionHelper;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionImportRepository $dataDefinitionImportRepository
    * @param DataDefinitionHelper $dataDefinitionHelper
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionImportRepository $dataDefinitionImportRepository,
        DataDefinitionHelper $dataDefinitionHelper
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
        $this->setView('definition/import/detail');

        $request = $this->request;
        $id = (int) $request->getAttribute('id');

        /** @var DataDefinitionImport|null $dataDefinitionImport */
        $dataDefinitionImport = $this->dataDefinitionImportRepository->find($id);

        if ($dataDefinitionImport === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_import_list');
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
            $this->addSidebar('', 'Executer', 'data_definition_import_detail', ['id' => $id], ['confirm' => true]);
        }

        return $this->viewResponse();
    }
}
