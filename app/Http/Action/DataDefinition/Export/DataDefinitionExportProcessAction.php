<?php

declare(strict_types=1);

namespace Platine\App\Http\Action\DataDefinition\Export;

use Platine\App\Enum\DataDefinitionDirection;
use Platine\App\Enum\YesNoStatus;
use Platine\App\Helper\ActionHelper;
use Platine\App\Helper\DataDefinitionHelper;
use Platine\App\Helper\Filter;
use Platine\App\Http\Action\BaseAction;
use Platine\App\Model\Entity\DataDefinition;
use Platine\App\Model\Repository\DataDefinitionFieldRepository;
use Platine\App\Model\Repository\DataDefinitionRepository;
use Platine\Container\ContainerInterface;
use Platine\Framework\Http\Response\FileResponse;
use Platine\Http\ResponseInterface;
use Throwable;

/**
* @class DataDefinitionExportProcessAction
* @package Platine\App\Http\Action\DataDefinition\Export
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
     * The DataDefinitionHelper instance
     * @var DataDefinitionHelper
     */
    protected DataDefinitionHelper $dataDefinitionHelper;

    /**
     * The container instance
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
    * Create new instance
    * @param ActionHelper $actionHelper
    * @param DataDefinitionRepository $dataDefinitionRepository
    * @param DataDefinitionFieldRepository $dataDefinitionFieldRepository
    * @param DataDefinitionHelper $dataDefinitionHelper
    * @param ContainerInterface $container
    */
    public function __construct(
        ActionHelper $actionHelper,
        DataDefinitionRepository $dataDefinitionRepository,
        DataDefinitionFieldRepository $dataDefinitionFieldRepository,
        DataDefinitionHelper $dataDefinitionHelper,
        ContainerInterface $container
    ) {
        parent::__construct($actionHelper);
        $this->dataDefinitionRepository = $dataDefinitionRepository;
        $this->dataDefinitionFieldRepository = $dataDefinitionFieldRepository;
        $this->dataDefinitionHelper = $dataDefinitionHelper;
        $this->container = $container;
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView('definition/export/process');
        $param = $this->param;
        $request = $this->request;

        $id = (int) $request->getAttribute('id');

        /** @var DataDefinition|null $dataDefinition */
        $definition = $this->dataDefinitionRepository->filters([
                                                        'status' => YesNoStatus::YES,
                                                        'direction' => DataDefinitionDirection::OUT
                                                    ])
                                                    ->find($id);

        if ($definition === null) {
            $this->flash->setError($this->lang->tr('Cet enregistrement n\'existe pas'));

            return $this->redirect('data_definition_export_list');
        }

        $this->addContext('definition', $definition);

        $exportPath = $this->config->get('platform.data_export_path');
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
