<?php

declare(strict_types=1);

namespace Platine\App\Module\Etl\Action\Export;

use Platine\App\Enum\YesNoStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\Filter;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Module\Etl\Entity\DataDefinition;
use Platine\App\Module\Etl\Enum\DataDefinitionDirection;
use Platine\App\Module\Etl\Helper\EtlHelper;
use Platine\App\Module\Etl\Repository\DataDefinitionFieldRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionRepository;
use Platine\App\Module\Etl\Repository\DataDefinitionUserRepository;
use Platine\Container\ContainerInterface;
use Platine\Framework\Http\Response\FileResponse;
use Platine\Http\ResponseInterface;
use Platine\Stdlib\Helper\Arr;
use Throwable;

/**
* @class DataDefinitionExportProcessAction
* @package Platine\App\Module\Etl\Action\Export
*/
class DataDefinitionExportProcessAction extends BaseAction
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
     * The EtlHelper instance
     * @var EtlHelper
     */
    protected EtlHelper $dataDefinitionHelper;

    /**
     * The container instance
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
    * The DataDefinitionUserRepository instance
    * @var DataDefinitionUserRepository
    */
    protected DataDefinitionUserRepository $dataDefinitionUserRepository;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
    * @param EtlHelper $dataDefinitionHelper
    * @param DataDefinitionUserRepository $dataDefinitionUserRepository
    * @param ContainerInterface $container
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository,
        EtlHelper $dataDefinitionHelper,
        DataDefinitionUserRepository $dataDefinitionUserRepository,
        ContainerInterface $container
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->dataDefinitionHelper = $dataDefinitionHelper;
        $this->container = $container;
        $this->dataDefinitionUserRepository = $dataDefinitionUserRepository;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('etl/export/process');
        $param = $this->param;
        $request = $this->request;

        $definitionList = $this->dataDefinitionUserRepository->query()
                                                       ->filter(['user' => $this->authHelper->getUserId()])
                                                        ->all(['data_definition_id'], false);

        $definitionsId = Arr::getColumn($definitionList, ['data_definition_id']);

        $id = (int) $request->getAttribute('id');

        $definition = null;
        if (count($definitionsId) > 0) {
            /** @var DataDefinition|null $definition */
            $definition = $this->dataDefinitionRepository->filters([
                                                        'status' => YesNoStatus::YES,
                                                        'direction' => DataDefinitionDirection::OUT,
                                                        'definitions' => $definitionsId,
                                                    ])
                                                    ->find($id);
        }

        if ($definition === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_export_list');
        }

        $this->addContext('definition', $definition);

        $exportPath = $this->config->get('platform.data_temp_path');
        // if no filter export directly
        if ($definition->filter === null) {
            try {
                $exportFile = $this->dataDefinitionHelper->export($definition, $exportPath);

                return new FileResponse($exportFile);
            } catch (Throwable $ex) {
                $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

                $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

                return $this->redirect('data_definition_export_list');
            }
        }

        /** @var Filter $filter */
        $filter = $this->container->get($definition->filter);
        $this->addContext('filters', $filter->form());

        if ($request->getMethod() === 'GET') {
            return $this->viewResponse();
        }

        $filterData = $param->posts();
        $filter->setParams(array_filter($filterData));


        try {
            $exportFile = $this->dataDefinitionHelper->export($definition, $exportPath, $filter->getParams());

            return new FileResponse($exportFile);
        } catch (Throwable $ex) {
            $this->logger->error('Error when saved the data {error}', ['error' => $ex->getMessage()]);

            $this->flash->setError($this->lang->tr('Erreur lors de traitement des données'));

            return $this->viewResponse();
        }
    }
}
